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

namespace local_devtools\local\lint\schemas\issue;

use local_devtools\local\lint\schemas\issue;
use local_devtools\local\lint\severity;
use function in_array;

/**
 * Class representing a single stylelint linter issue.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-import-type issue_data from issue
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stylelint extends issue {
    /**
     * Factory method to create from an stylelint warning object.
     * @param object $object
     * @return self|null
     */
    public static function from_object(object $object): ?self {
        $line = self::object_property($object, 'line');
        $column = self::object_property($object, 'column');
        $rule = self::object_property($object, 'rule');
        $severity = self::object_property($object, 'severity');
        $text = self::object_property($object, 'text');
        // The message also includes nodeType, messageId, endLine, endColumn, but we won't use it.

        if (in_array(null, [$line, $column, $severity, $text], strict: true)) {
            return null;
        }

        return new self(
            $line,
            $column,
            $text,
            $rule,
            'stylelint',
            severity::from_stylelint($severity),
        );
    }
}
