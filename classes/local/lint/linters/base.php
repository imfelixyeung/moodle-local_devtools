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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The abstract base linter.
 *
 * // phpcs:ignore moodle.Commenting.ValidTags.Invalid
 * @phpstan-type FilesWithIssues array{file: string, issues: issue[]}
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base {
    /**
     * Declares file patterns to match for.
     * @return string[]
     */
    public static function get_patterns(): array {
        return ['*'];
    }

    /**
     * Lints a single file.
     * @param string $filepath
     * @return FilesWithIssues
     */
    public function lint_file(string $filepath): array {
        $result = [
            'file' => $filepath,
            'issues' => [],
        ];
        if (!$this->can_lint_file($filepath)) {
            return $result;
        }

        if (!file_exists($filepath)) {
            $result['issues'][] = new issue(
                0,
                0,
                "File not found",
                "file-must-exist",
                "base",
                severity::error
            );
        }

        return [$result];
    }

    /**
     * Lints a single directory.
     * @param string $directorypath
     * @return FilesWithIssues
     */
    public function lint_directory(string $directorypath): array {
        $results = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorypath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $path) {
            $results[] = $this->lint_file($path);
        }

        return $results;
    }

    /**
     * Lints a given path.
     * @param string $path
     * @return FilesWithIssues
     */
    public function lint(string $path): array {
        if (is_dir($path)) {
            return $this->lint_directory($path);
        }

        if (is_file($path)) {
            return $this->lint_file($path);
        }

        return [
            'file' => $path,
            'issues' => [
                new issue(
                    0,
                    0,
                    "Path not found",
                    "path-must-exist",
                    "base",
                    severity::error
                ),
            ],
        ];
    }

    /**
     * Checks if a given filepath can be linted by the current linter.
     * @param string $filepath
     * @return bool
     */
    public function can_lint_file(string $filepath): bool {
        // As long as it matches one of the PATTERNS, it passes.
        foreach (static::get_patterns() as $pattern) {
            $match = fnmatch($pattern, $filepath);
            if (!$match) {
                continue;
            }

            return true;
        }

        return false;
    }
}
