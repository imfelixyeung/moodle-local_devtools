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

namespace local_devtools\local\config;

use local_devtools\local\devtools;

/**
 * Utility class to get plugin config.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debugbar {
    /**
     * Get if debugbar is enabled.
     * @return bool True if enabled, false otherwise.
     */
    public static function is_enabled(): bool {
        $pluginenabled = devtools::is_enabled();
        if (!$pluginenabled) {
            return false;
        }

        return get_config('local_devtools', 'debugbar_enabled') === '1';
    }

    /**
     * Get if debugbar query collection is enabled.
     * @return bool True if enabled, false otherwise.
     */
    public static function is_collect_queries_enabled(): bool {
        return get_config('local_devtools', 'debugbar_collect_queries') === '1';
    }

    /**
     * Get the configured editor for the plugin.
     * @return string|null The editor name or null if not set.
     */
    public static function get_editor(): ?string {
        return get_config('local_devtools', 'debugbar_editor') ?: null;
    }
}
