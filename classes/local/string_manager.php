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

/**
 * Custom string manager to intercept any get_string calls.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_devtools\local;

use core_string_manager_standard;

/**
 * Intercepts and logs any get_string calls.
 *
 * {@inheritDoc}
 */
class string_manager extends core_string_manager_standard {
    // phpcs:disable moodle.Commenting.InlineComment
    // @phpstan-ignore missingType.iterableValue
    #[\Override]
    public function get_string($identifier, $component = '', $a = null, $lang = null) {
        $result = parent::get_string($identifier, $component, $a, $lang);
        if (!devtools::is_enabled() || !\local_devtools\local\config\debugbar::is_enabled()) {
            return $result;
        }

        // Do nothing if the collector is not enabled.
        $collector = debugbar::instance()->get_string_manager_collector();
        if (!$collector) {
            return $result;
        }

        // Log it.
        $label = $component
            ? "get_string('$identifier', '$component')"
            : "get_string('$identifier')";
        $params = [
            'identifier' => $identifier,
            'component' => $component,
            'a' => $a,
            'lang' => $lang,
            'string' => $result,
        ];
        $collector->addMeasure($label, params: $params);

        return $result;
    }
}
