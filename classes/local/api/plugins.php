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

use core\plugin_manager;

/**
 * Plugins API.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugins {
    /**
     * List installed plugins
     * @param bool $includestandard
     * @return array{
     *   component: string,
     *   directory: string,
     *   enabled: bool|null,
     *   name: string,
     *   release: mixed,
     *   standard: bool,
     *   type: string,
     *   version: int|string
     * }[]
     */
    public static function list(bool $includestandard = false) {
        $manager = plugin_manager::instance();
        /** @var array<string, array<string, \core\plugininfo\base>> $plugininfo */
        $plugininfo = $manager->get_plugins();

        $results = [];

        foreach ($plugininfo as $type => $plugins) {
            foreach ($plugins as $name => $plugin) {
                $isstandard = $plugin->is_standard();
                if (!$includestandard && $isstandard) {
                    continue;
                }

                $results[] = [
                    'type' => $plugin->type,
                    'name' => $plugin->name,
                    'version' => $plugin->versiondb,
                    'release' => $plugin->release,
                    'component' => $plugin->component,
                    'directory' => $plugin->rootdir,
                    'standard' => $isstandard,
                    'enabled' => $plugin->is_enabled(),
                ];
            }
        }

        return $results;
    }
}
