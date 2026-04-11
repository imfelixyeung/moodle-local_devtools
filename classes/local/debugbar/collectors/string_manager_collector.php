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

use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;

// phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod

/**
 * Collector to display Moodle information.
 * Like the one seen on Laravel Debugbar https://laraveldebugbar.com/
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class string_manager_collector extends TimeDataCollector implements Renderable {
    #[\Override]
    public function getName(): string {
        return 'string_manager';
    }

    #[\Override]
    public function getWidgets(): array {
        return [
            "string_manager" => [
                "icon" => "chart-infographic",
                "widget" => "PhpDebugBar.Widgets.TimelineWidget",
                "map" => "string_manager",
                "default" => "{}",
            ],
        ];
    }
}
