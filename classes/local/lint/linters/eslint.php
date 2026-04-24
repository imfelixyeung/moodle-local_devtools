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

use local_devtools\local\lint\issue;
use Symfony\Component\Process\Process;

/**
 * The eslint linter.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eslint extends base {
    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            ...['*.js'],
        ];
    }

    #[\Override]
    public static function get_exclude_patterns(): array {
        return [
            ...parent::get_exclude_patterns(),
            ...['**/amd/build/**', '**/yui/build/**'],
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

        $process = new Process(['bunx', 'eslint', '--format', 'json', $filepath]);
        $process->run();

        $output = $process->getOutput();
        $jsonoutput = json_decode($output);
        if ($jsonoutput === null) {
            return [];
        }

        foreach ($jsonoutput as $lintedfile) {
            $issues = [];
            $messages = $lintedfile->messages;
            foreach ($messages as $message) {
                $issue = issue::from_eslint_message($message);
                if ($issue) {
                    $issues[] = $issue;
                }
            }

            $results[] = [
                'file' => $lintedfile->filePath,
                'issues' => $issues,
            ];
        }

        return $results;
    }
}
