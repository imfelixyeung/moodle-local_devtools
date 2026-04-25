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

namespace local_devtools\local\mcp\tools;

use local_devtools\local\api\linter;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;

/**
 * Lints files.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-import-type FileWithIssues from \local_devtools\local\lint\linters\base
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lint {
    /**
     * Runs moodle coding standard linters against files or directories.
     * @param string[] $paths absolute paths to files or directories that needs linting
     * @return object{
     *     linters: string[], // list of linters that have run
     *     files: FileWithIssues[], // list of files and their issues
     * }
     */
    #[McpTool(
        name: 'lint_files',
        description: 'Runs project coding standard linters against files or directories',
        annotations: new ToolAnnotations(readOnlyHint: true, destructiveHint: false, idempotentHint: true),
    )]
    public static function lint_files(array $paths): object {
        global $CFG;
        $cwd = getcwd();

        chdir($CFG->root);
        $linters = linter::get_linters_classnames();
        $results = linter::run($paths, $linters);
        chdir($cwd);

        return (object) [
            'linters' => linter::get_linters_info($linters),
            'files' => $results,
        ];
    }
}
