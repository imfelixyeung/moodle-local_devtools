<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_devtools\local\lint\linters;

use local_devtools\local\lint\schemas\issue;
use local_devtools\local\lint\severity;
use local_devtools\local\lint\schemas\file;
use Symfony\Component\Process\Process;

/**
 * The moodle-local_moodlecheck phpdoc linter.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phpdoc extends base {
    #[\Override]
    public static function get_description(): ?string {
        return 'executes moodle-local_moodlecheck phpdoc linter';
    }

    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            '*.php',
        ];
    }

    #[\Override]
    public function lint_file(string $filepath): array {
        $results = parent::lint_file($filepath);
        if (!$this->can_lint_file($filepath)) {
            return $results;
        }

        $filepath = realpath($filepath);
        if ($filepath === false) {
            return [];
        }

        $fileresult = new file($filepath);

        $installedpath = self::get_installed_path();
        if (!$installedpath) {
            return [];
        }

        $this->set_progress_file($filepath);
        $process = new Process(['php', $installedpath, '--path=' . $filepath]);
        $process->run();

        $output = $process->getOutput();

        if (!$output) {
            $results[] = $fileresult;
            return $results;
        }

        $xmlarr = (new \core\xml_parser())->parse($output);
        if ($xmlarr === false) {
            $fileresult->add_issue(issue::simple(
                "Output: '$output'",
                'linter-returned-invalid-results',
                $this->get_name(),
                severity::error,
            ));
            return [$fileresult];
        }

        $filehash = $xmlarr['file']['#'];
        if (!$filehash) {
            $results[] = $fileresult;
            return $results;
        }

        $errors = $filehash['error'];

        foreach ($errors as $error) {
            $errordata = $error['@'];
            $issue = \local_devtools\local\lint\schemas\issue\phpdoc::from_object((object) $errordata);
            if (!$issue) {
                continue;
            }
            $fileresult->add_issue($issue);
        }

        $results[] = $fileresult;
        return $results;
    }

    /**
     * Gets the installed path.
     * @return string|null
     */
    private static function get_installed_path(): ?string {
        /** @var string|null $cache */
        static $cache;
        if (isset($cache)) {
            return $cache;
        }

        global $CFG;
        $path = $CFG->dirroot . '/local/moodlecheck/cli/moodlecheck.php';
        $realpath = realpath($path);
        $cache = $realpath ?: null;

        return $cache;
    }

    #[\Override]
    public static function is_installed(): bool {
        return self::get_installed_path() !== null;
    }
}
