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
 * Class representing a single eslint linter issue.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eslint extends issue {
    /**
     * Factory method to create from an eslint message object.
     * @param object $object
     * @return self|null
     */
    public static function from_object(object $object): ?self {
        $ruleid = self::object_property($object, 'ruleId');
        $severity = self::object_property($object, 'severity', 0);
        $message = self::object_property($object, 'message');
        $line = self::object_property($object, 'line');
        $column = self::object_property($object, 'column');
        // The message also includes nodeType, messageId, endLine, endColumn, but we won't use it.

        // Some messages return empty ruleId, ignore those for now.
        if (!$ruleid) {
            return null;
        }

        if (in_array(null, [$line, $column, $severity, $message], strict: true)) {
            return null;
        }

        return new self(
            $line,
            $column,
            $message,
            $ruleid,
            'eslint',
            severity::from_eslint($severity),
        );
    }
}
