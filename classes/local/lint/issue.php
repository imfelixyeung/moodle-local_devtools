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
 * Class representing a single linter issue.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue {
    /** @var int Line number of the issue */
    public int $line;
    /** @var int Column of the issue */
    public int $column;
    /** @var string Message of the issue */
    public string $message;
    /** @var string The linter rule used */
    public ?string $rule;
    /** @var string The error source */
    public string $source;
    /** @var severity The severity of the issue */
    public severity $severity;

    /**
     * Constructor.
     * @param int $line
     * @param int $column
     * @param string $message
     * @param string|null $rule
     * @param string $source
     * @param severity $severity
     */
    public function __construct(
        int $line,
        int $column,
        string $message,
        ?string $rule,
        string $source,
        severity $severity,
    ) {
        $this->line = $line;
        $this->column = $column;
        $this->message = $message;
        $this->rule = $rule;
        $this->source = $source;
        $this->severity = $severity;
    }

    /**
     * Factory method to create from an eslint message.
     * @param object $messageobj
     * @return self|null
     */
    public static function from_eslint_message(object $messageobj): ?self {
        $ruleid = self::object_property($messageobj, 'ruleId');
        $severity = self::object_property($messageobj, 'severity', 0);
        $message = self::object_property($messageobj, 'message');
        $line = self::object_property($messageobj, 'line');
        $column = self::object_property($messageobj, 'column');
        // The message also includes nodeType, messageId, endLine, endColumn, but we won't use it.

        // Some messages return empty ruleId, ignore those for now.
        if (!$ruleid) {
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

    /**
     * Factory method to create from an stylelint warning.
     * @param object $warningobj
     * @return self|null
     */
    public static function from_stylelint_warning(object $warningobj): ?self {
        $line = self::object_property($warningobj, 'line');
        $column = self::object_property($warningobj, 'column');
        $rule = self::object_property($warningobj, 'rule');
        $severity = self::object_property($warningobj, 'severity');
        $text = self::object_property($warningobj, 'text');
        // The message also includes nodeType, messageId, endLine, endColumn, but we won't use it.

        return new self(
            $line,
            $column,
            $text,
            $rule,
            'stylelint',
            severity::from_stylelint($severity),
        );
    }

    /**
     * Factory method to create from an phpcs warning.
     * @param object $messageobj
     * @return self|null
     */
    public static function from_phpcs_message(object $messageobj): ?self {
        $line = self::object_property($messageobj, 'line');
        $column = self::object_property($messageobj, 'column');
        $source = self::object_property($messageobj, 'source');
        $severity = self::object_property($messageobj, 'severity');
        $message = self::object_property($messageobj, 'message');

        return new self(
            $line,
            $column,
            $message,
            $source,
            'phpcs',
            severity::from_phpcs($severity),
        );
    }

    /**
     * Utility function to get an object's property value, with fallback value.
     * @param object $object
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    private static function object_property(object $object, string $property, mixed $default = null): mixed {
        if (!property_exists($object, $property)) {
            return $default;
        }

        return $object->{$property};
    }
}
