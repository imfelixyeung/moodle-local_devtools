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
 * The 'php -l' linter.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phplint extends base {
    #[\Override]
    public static function get_description(): ?string {
        return 'executes "php -l" for syntax checking';
    }

    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            ...['*.php'],
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

        $this->set_progress_file($filepath);
        $process = new Process(['php', '-l', $filepath]);
        $process->run();

        if ($process->isSuccessful()) {
            $results[] = $fileresult;
            return $results;
        }

        $output = $process->getOutput();
        $fileresult->add_issue(new issue(
            0,
            0,
            trim($output),
            'php-file-must-parse-successfully',
            $this->get_name(),
            severity::error,
        ));

        $results[] = $fileresult;
        return $results;
    }
}
