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

namespace local_devtools\local\debugbar\pdo;

use DebugBar\DataCollector\PDO\TracedStatement;
use local_devtools\local\databases\mariadb_native_devtools_database;
use local_devtools\local\databases\mysqli_native_devtools_database;
use mariadb_native_moodle_database;
use moodle_database;
use mysqli_native_moodle_database;
use function in_array;

/**
 * Traced statement with backtrace support.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * Helper type aliases for static analysis.
 * @phpstan-type BacktraceFrame array{file?: string, line?: int, class?: string, function?: string}
 * @phpstan-type Backtrace BacktraceFrame[]
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class traced_statement extends TracedStatement {
    // phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
    #[\Override]
    public function checkBacktrace(int $limit = 15): void {
        // Internal calls, so we skip them.
        static $blacklistedclasses = [
        moodle_database::class,
        mysqli_native_devtools_database::class,
        mysqli_native_moodle_database::class,
        mariadb_native_devtools_database::class,
        mariadb_native_moodle_database::class,
        self::class,
        ];

        \Symfony\Component\VarDumper\VarDumper::dump($blacklistedclasses);

        /** @var Backtrace $backtrace */
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $lastframe = array_pop($backtrace);

        $backtrace = array_filter($backtrace, function ($frame) use ($blacklistedclasses) {
            if (!isset($frame['class'])) {
                return true;
            }
            return !in_array($frame['class'], $blacklistedclasses, true);
        });

        // Add last frame back.
        if ($lastframe) {
            $backtrace[] = $lastframe;
        }

        $backtrace = array_slice($backtrace, 0, $limit);

        \Symfony\Component\VarDumper\VarDumper::dump($backtrace);

        $this->backtrace = $backtrace;
        $this->sql = "$this->sql\n" . $this->format_backtrace($backtrace);
        return;
    }
    // phpcs:enable moodle.NamingConventions.ValidFunctionName.LowercaseMethod

    /**
     * Format a backtrace array into a string for logging.
     * @param Backtrace $backtrace
     * @return string
     */
    protected function format_backtrace(array $backtrace): string {
        $formattedframes = array_map([$this, 'format_backtrace_frame'], $backtrace);
        $formattedframes = array_filter($formattedframes);
        return implode("\n", $formattedframes);
    }

    /**
     * Format a single backtrace frame for logging.
     * @param BacktraceFrame $frame
     * @return string|null
     */
    protected function format_backtrace_frame(array $frame): ?string {
        $location = isset($frame['file'], $frame['line'])
            ? "{$frame['file']}:{$frame['line']}"
            : 'unknown location';

        if (isset($frame['class'], $frame['function'])) {
            return "-- at {$frame['class']}::{$frame['function']}() in $location";
        }

        if (isset($frame['function'])) {
            return "-- at {$frame['function']}() in $location";
        }

        return null;
    }
}
