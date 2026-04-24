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
     * Declares file patterns to include.
     * @return string[]
     */
    public static function get_include_patterns(): array {
        return [];
    }

    /**
     * Declares file patterns to exclude.
     * @return string[]
     */
    public static function get_exclude_patterns(): array {
        return ['**/.git/**', '**/vendor/**'];
    }

    /**
     * Lints a single file.
     * @param string $filepath
     * @return FilesWithIssues
     */
    public function lint_file(string $filepath): array {
        if (!$this->can_lint_file($filepath)) {
            return [];
        }

        $result = [
            'file' => $filepath,
            'issues' => [],
        ];

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
            $lintresult = $this->lint_file($path);
            if ($lintresult) {
                $results[] = $lintresult;
            }
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
     * Checks if a given path matches some patterns.
     * @param string $path
     * @param string[] $patterns
     * @return bool
     */
    private function path_match_patterns($path, $patterns): bool {
        // As long as it matches one of the PATTERNS.
        foreach ($patterns as $pattern) {
            $match = fnmatch($pattern, $path);
            if (!$match) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Checks if a given filepath can be linted by the current linter.
     * Must match one of the include patterns AND none of the exclude patterns.
     * @param string $filepath
     * @return bool
     */
    public function can_lint_file(string $filepath): bool {
        $includematch = $this->path_match_patterns($filepath, $this->get_include_patterns());
        if (!$includematch) {
            return false;
        }

        $excludematch = $this->path_match_patterns($filepath, $this->get_exclude_patterns());
        if ($excludematch) {
            return false;
        }

        return true;
    }
}
