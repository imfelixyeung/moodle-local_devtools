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

namespace local_devtools\local\lint;

/**
 * Enum for severity.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
enum severity: string {
    case info = 'info';
    case warning = 'warning';
    case error = 'error';
    case unknown = 'unknown';

    /**
     * Gets severity from eslint.
     * @param int $severity
     * @return severity
     */
    public static function from_eslint(int $severity) {
        return match ($severity) {
            0 => self::info,
            1 => self::warning,
            2 => self::error,
            default => self::unknown,
        };
    }

    /**
     * Gets severity from stylelint.
     * @param string $severity
     * @return severity
     */
    public static function from_stylelint(string $severity) {
        return match ($severity) {
            'warning' => self::warning,
            'error' => self::error,
            default => self::unknown,
        };
    }

    /**
     * Gets severity from phpcs.
     * @param int $severity
     * @return severity
     */
    public static function from_phpcs(int $severity) {
        return match (true) {
            $severity <= 0 => self::info,
            $severity <= 4 => self::warning,
            $severity === 5 => self::error,
            default => self::unknown,
        };
    }
}
