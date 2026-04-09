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

namespace local_devtools\local\debugbar\collectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

// phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod

/**
 * Collector to display Moodle information.
 * Like the one seen on Laravel Debugbar https://laraveldebugbar.com/
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle_collector extends DataCollector implements Renderable {
    /**
     * {@inheritDoc}
     */
    public function collect(): array {
        global $CFG;
        [$version] = explode(' ', $CFG->release);
        $lang = current_language();
        return [
            "version" => $version,
            'tooltip' => [
                'Moodle Version' => $CFG->release,
                'PHP Version' => implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]) . PHP_EXTRA_VERSION,
                'Debug Mode' => $CFG->debugdeveloper ? 'Enabled' : 'Disabled',
                'Lang' => get_string('thislanguage', 'langconfig') . " ($lang)",
            ],
        ];
    }

    #[\Override]
    public function getName(): string {
        return 'moodle';
    }

    #[\Override]
    public function getWidgets(): array {
        return [
            "moodle_info" => [
                "icon" => "brand-moodle",
                "map" => "moodle.version",
                "default" => "",
                "tooltip" => "moodle.tooltip",
            ],
            "moodle_info:tooltip" => [
                "map" => "moodle.tooltip",
                "default" => "{}",
            ],
        ];
    }
}
