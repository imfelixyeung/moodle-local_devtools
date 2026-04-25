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

namespace local_devtools\local\lint\schemas;

use JsonSerializable;
use local_devtools\local\lint\severity;

/**
 * Class representing a single linter issue.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-type issue_data array{
 *     line: int,
 *     column: int,
 *     message: string,
 *     rule: string|null,
 *     source: string,
 *     severity: severity,
 * }
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue implements JsonSerializable {
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
     * Factory method to create from a linter result object.
     * @param object $object
     * @return self|null
     */
    public static function from_object(object $object): ?self {
        // To be overridden.
        return null;
    }

    /**
     * Utility function to get an object's property value, with fallback value.
     * @param object $object
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    protected static function object_property(object $object, string $property, mixed $default = null): mixed {
        if (!property_exists($object, $property)) {
            return $default;
        }

        return $object->{$property};
    }

    /**
     * Helper function to create a simple issue.
     * @param string $message
     * @param string|null $rule
     * @param string $source
     * @param severity $severity
     * @return self
     */
    public static function simple(
        string $message,
        ?string $rule = null,
        string $source = 'unknown',
        severity $severity = severity::error,
    ): self {
        return new self(
            0,
            0,
            $message,
            $rule,
            $source,
            $severity,
        );
    }

    /**
     * Get data to be serialised.
     * @return issue_data
     */
    public function jsonSerialize(): array {
        return [
            'line' => $this->line,
            'column' => $this->column,
            'message' => $this->message,
            'rule' => $this->rule,
            'source' => $this->source,
            'severity' => $this->severity,
        ];
    }
}
