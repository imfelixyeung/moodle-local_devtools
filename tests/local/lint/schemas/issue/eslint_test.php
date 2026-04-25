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
 * Unit tests for the eslint issue class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\schemas\issue\eslint
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class eslint_test extends advanced_testcase {
    /**
     * Test that from_object creates issue from valid eslint object.
     */
    public function test_from_object_creates_issue_from_valid_object(): void {
        $obj = (object) [
            'ruleId' => 'no-unused-vars',
            'severity' => 2,
            'message' => 'Unused variable',
            'line' => 5,
            'column' => 10,
        ];

        $issue = eslint::from_object($obj);

        $this->assertNotNull($issue);
        $this->assertSame(5, $issue->line);
        $this->assertSame(10, $issue->column);
        $this->assertSame('Unused variable', $issue->message);
        $this->assertSame('no-unused-vars', $issue->rule);
        $this->assertSame('eslint', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_object returns null when ruleId is empty.
     */
    public function test_from_object_returns_null_when_rule_id_empty(): void {
        $obj = (object) [
            'ruleId' => '',
            'severity' => 2,
            'message' => 'Some message',
            'line' => 1,
            'column' => 1,
        ];

        $result = eslint::from_object($obj);
        $this->assertNull($result);
    }

    /**
     * Test that from_object uses default severity when not provided.
     */
    public function test_from_object_uses_default_severity_when_missing(): void {
        $obj = (object) [
            'ruleId' => 'some-rule',
            'message' => 'Message',
            'line' => 1,
            'column' => 1,
        ];

        $issue = eslint::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::info, $issue->severity);
    }

    /**
     * Test that from_object maps severity 0 to info.
     */
    public function test_from_object_maps_severity_0_to_info(): void {
        $obj = (object) [
            'ruleId' => 'some-rule',
            'severity' => 0,
            'message' => 'Message',
            'line' => 1,
            'column' => 1,
        ];

        $issue = eslint::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::info, $issue->severity);
    }

    /**
     * Test that from_object maps severity 1 to warning.
     */
    public function test_from_object_maps_severity_1_to_warning(): void {
        $obj = (object) [
            'ruleId' => 'some-rule',
            'severity' => 1,
            'message' => 'Message',
            'line' => 1,
            'column' => 1,
        ];

        $issue = eslint::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::warning, $issue->severity);
    }

    /**
     * Test that from_object returns null when empty object is passed.
     */
    public function test_from_object_handles_empty_object(): void {
        $obj = (object) [];
        $issue = eslint::from_object($obj);
        $this->assertNull($issue);
    }
}
