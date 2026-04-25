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
use local_devtools\local\lint\severity;
use Symfony\Component\Process\Process;

/**
 * The 'php -l' linter.
 *
 * // phpcs:ignore moodle.Commenting.ValidTags.Invalid
 * @phpstan-import-type FileWithIssues from base
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phpcs extends base {
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

        $this->set_progress_file($filepath);
        return [...$results, ...$this->execute_phpcs($filepath)];
    }

    #[\Override]
    public function lint_directory(string $directorypath): array {
        $this->set_progress_file($directorypath);
        return $this->execute_phpcs($directorypath);
    }

    /**
     * Executes phpcs on a given path.
     * @param string $path
     * @return FileWithIssues[]
     */
    private function execute_phpcs($path): array {
        $process = new Process(['phpcs', '--cache', '-q', '--report=json', $path], timeout: MINSECS * 15);
        $process->run();

        $output = $process->getOutput();
        return $this->parse_phpcs_json($output, $path);
    }

    /**
     * Parses the PHPCS JSON result.
     * @param string $output
     * @param string $path
     * @return FileWithIssues[]
     */
    private function parse_phpcs_json(string $output, string $path) {
        $results = [];
        $jsonoutput = json_decode($output);
        if ($jsonoutput === null) {
            $results[] = [
                'file' => (string) $path,
                'issues' => [
                    new issue(
                        0,
                        0,
                        "'phpcs' returned non-JSON output.",
                        'phpcs-json-error',
                        $this->get_name(),
                        severity::error,
                    ),
                ],
            ];
            return $results;
        }

        foreach ($jsonoutput->files as $path => $lintedfile) {
            $issues = [];
            $messages = $lintedfile->messages;
            foreach ($messages as $message) {
                $issue = issue::from_phpcs_message($message);
                if ($issue) {
                    $issues[] = $issue;
                }
            }

            $results[] = [
                'file' => (string) $path,
                'issues' => $issues,
            ];
        }

        return $results;
    }
}
