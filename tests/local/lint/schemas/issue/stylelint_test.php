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

declare(strict_types=1);

namespace local_devtools\local\lint\schemas\issue;

use advanced_testcase;
use local_devtools\local\lint\severity;

/**
 * Unit tests for the stylelint issue class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\schemas\issue\stylelint
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class stylelint_test extends advanced_testcase {
    /**
     * Test that from_object creates issue from stylelint warning.
     */
    public function test_from_object_creates_issue(): void {
        $obj = (object) [
            'line' => 3,
            'column' => 5,
            'rule' => 'color-no-invalid-hex',
            'severity' => 'error',
            'text' => 'Unexpected invalid hex color',
        ];

        $issue = stylelint::from_object($obj);

        $this->assertNotNull($issue);
        $this->assertSame(3, $issue->line);
        $this->assertSame(5, $issue->column);
        $this->assertSame('Unexpected invalid hex color', $issue->message);
        $this->assertSame('color-no-invalid-hex', $issue->rule);
        $this->assertSame('stylelint', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_object maps 'warning' severity.
     */
    public function test_from_object_maps_warning_severity(): void {
        $obj = (object) [
            'line' => 1,
            'column' => 1,
            'rule' => 'some-rule',
            'severity' => 'warning',
            'text' => 'Warning message',
        ];

        $issue = stylelint::from_object($obj);
        $this->assertSame(severity::warning, $issue->severity);
    }

    /**
     * Test that from_object maps unknown severity to unknown.
     */
    public function test_from_object_maps_unknown_severity_to_unknown(): void {
        $obj = (object) [
            'line' => 1,
            'column' => 1,
            'rule' => 'some-rule',
            'severity' => 'invalid',
            'text' => 'Message',
        ];

        $issue = stylelint::from_object($obj);
        $this->assertSame(severity::unknown, $issue->severity);
    }

    /**
     * Test that from_object returns null when empty object is passed.
     */
    public function test_from_object_handles_empty_object(): void {
        $obj = (object) [];
        $issue = stylelint::from_object($obj);
        $this->assertNull($issue);
    }
}
