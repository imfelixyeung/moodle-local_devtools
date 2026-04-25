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

namespace local_devtools\local\api;

use local_devtools\local\lint\linters\base;
use local_devtools\local\lint\linters\eslint;
use local_devtools\local\lint\linters\lang;
use local_devtools\local\lint\linters\phpcs;
use local_devtools\local\lint\linters\phplint;
use local_devtools\local\lint\linters\stylelint;
use Symfony\Component\Console\Helper\ProgressIndicator;

/**
 * Linter API.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-import-type FileWithIssues from base
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class linter {
    /**
     * Utility function to get enabled linters.
     * @param bool $eslint
     * @param bool $lang
     * @param bool $phpcs
     * @param bool $phplint
     * @param bool $stylelint
     * @return class-string<base>[]
     */
    public static function get_linters_classnames(
        bool $eslint = true,
        bool $lang = true,
        bool $phpcs = true,
        bool $phplint = true,
        bool $stylelint = true,
    ): array {
        $linters = [
            $eslint ? eslint::class : null,
            $lang ? lang::class : null,
            $phpcs ? phpcs::class : null,
            $phplint ? phplint::class : null,
            $stylelint ? stylelint::class : null,
        ];
        $linters = array_filter($linters, fn($linter) => $linter !== null);
        return $linters;
    }

    /**
     * Utility function to get enabled linters.
     * @param class-string<base>[] $linters
     * @return string[]
     */
    public static function get_linters_info(array $linters): array {
        $info = array_values(array_map(
            fn(/** @var class-string<base> $linter */ $linter) => $linter::get_name(),
            $linters
        ));
        return $info;
    }

    /**
     * Executes linters on paths
     * @param string[] $paths
     * @param class-string<base>[] $linterclasses
     * @param ProgressIndicator $progress
     * @return FileWithIssues[]
     */
    public static function run(array $paths, array $linterclasses, ?ProgressIndicator $progress = null): array {
        $linters = array_map(fn(/** @var class-string<base> $linterclass */ $linterclass) => new $linterclass(), $linterclasses);

        $progress?->start('Starting.');
        $resultsitems = array_map(
            fn(string $path) => array_map(
                fn(base $linter) => $linter->lint($path),
                $linters
            ),
            $paths
        );
        $results = array_merge(...$resultsitems);
        $progress?->finish('Done.');

        return base::flatten_results($results);
    }
}
