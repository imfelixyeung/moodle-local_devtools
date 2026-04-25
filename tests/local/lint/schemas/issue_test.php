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

namespace local_devtools\local\lint\schemas;

use advanced_testcase;
use local_devtools\local\lint\severity;

/**
 * Unit tests for the issue class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\schemas\issue
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class issue_test extends advanced_testcase {
    /**
     * Test that constructor sets properties correctly.
     */
    public function test_constructor_sets_properties(): void {
        $issue = new issue(10, 20, 'Test message', 'rule1', 'test', severity::error);

        $this->assertSame(10, $issue->line);
        $this->assertSame(20, $issue->column);
        $this->assertSame('Test message', $issue->message);
        $this->assertSame('rule1', $issue->rule);
        $this->assertSame('test', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that constructor allows null rule.
     */
    public function test_constructor_allows_null_rule(): void {
        $issue = new issue(1, 1, 'Message', null, 'source', severity::warning);
        $this->assertNull($issue->rule);
    }

    /**
     * Test that from_eslint_message creates issue from valid eslint object.
     */
    public function test_from_eslint_message_creates_issue_from_valid_object(): void {
        $obj = (object) [
            'ruleId' => 'no-unused-vars',
            'severity' => 2,
            'message' => 'Unused variable',
            'line' => 5,
            'column' => 10,
        ];

        $issue = issue::from_eslint_message($obj);

        $this->assertNotNull($issue);
        $this->assertSame(5, $issue->line);
        $this->assertSame(10, $issue->column);
        $this->assertSame('Unused variable', $issue->message);
        $this->assertSame('no-unused-vars', $issue->rule);
        $this->assertSame('eslint', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_eslint_message returns null when ruleId is empty.
     */
    public function test_from_eslint_message_returns_null_when_rule_id_empty(): void {
        $obj = (object) [
            'ruleId' => '',
            'severity' => 2,
            'message' => 'Some message',
            'line' => 1,
            'column' => 1,
        ];

        $result = issue::from_eslint_message($obj);
        $this->assertNull($result);
    }

    /**
     * Test that from_eslint_message uses default severity when not provided.
     */
    public function test_from_eslint_message_uses_default_severity_when_missing(): void {
        $obj = (object) [
            'ruleId' => 'some-rule',
            'message' => 'Message',
            'line' => 1,
            'column' => 1,
        ];

        $issue = issue::from_eslint_message($obj);
        $this->assertSame(severity::info, $issue->severity);
    }

    /**
     * Test that from_stylelint_warning creates issue from stylelint warning.
     */
    public function test_from_stylelint_warning_creates_issue(): void {
        $obj = (object) [
            'line' => 3,
            'column' => 5,
            'rule' => 'color-no-invalid-hex',
            'severity' => 'error',
            'text' => 'Unexpected invalid hex color',
        ];

        $issue = issue::from_stylelint_warning($obj);

        $this->assertNotNull($issue);
        $this->assertSame(3, $issue->line);
        $this->assertSame(5, $issue->column);
        $this->assertSame('Unexpected invalid hex color', $issue->message);
        $this->assertSame('color-no-invalid-hex', $issue->rule);
        $this->assertSame('stylelint', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_phpcs_message creates issue from phpcs message.
     */
    public function test_from_phpcs_message_creates_issue(): void {
        $obj = (object) [
            'line' => 10,
            'column' => 15,
            'source' => 'Generic.WhiteSpace.ScopeIndent',
            'severity' => 5,
            'message' => 'Expected 4 spaces before',
        ];

        $issue = issue::from_phpcs_message($obj);

        $this->assertNotNull($issue);
        $this->assertSame(10, $issue->line);
        $this->assertSame(15, $issue->column);
        $this->assertSame('Expected 4 spaces before', $issue->message);
        $this->assertSame('Generic.WhiteSpace.ScopeIndent', $issue->rule);
        $this->assertSame('phpcs', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that simple creates issue with default values.
     */
    public function test_simple_creates_issue_with_defaults(): void {
        $issue = issue::simple('Simple error');

        $this->assertSame(0, $issue->line);
        $this->assertSame(0, $issue->column);
        $this->assertSame('Simple error', $issue->message);
        $this->assertNull($issue->rule);
        $this->assertSame('unknown', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that simple creates issue with custom severity.
     */
    public function test_simple_creates_issue_with_custom_severity(): void {
        $issue = issue::simple('Warning message', null, 'test', severity::warning);

        $this->assertSame(severity::warning, $issue->severity);
        $this->assertSame('test', $issue->source);
    }

    /**
     * Test that jsonSerialize returns correct structure.
     */
    public function test_json_serialize_returns_correct_structure(): void {
        $issue = new issue(5, 10, 'Error message', 'rule1', 'test', severity::error);
        $serialized = $issue->jsonSerialize();

        $this->assertArrayHasKey('line', $serialized);
        $this->assertArrayHasKey('column', $serialized);
        $this->assertArrayHasKey('message', $serialized);
        $this->assertArrayHasKey('rule', $serialized);
        $this->assertArrayHasKey('source', $serialized);
        $this->assertArrayHasKey('severity', $serialized);
        $this->assertSame(5, $serialized['line']);
        $this->assertSame(10, $serialized['column']);
        $this->assertSame('Error message', $serialized['message']);
        $this->assertSame('rule1', $serialized['rule']);
        $this->assertSame('test', $serialized['source']);
        $this->assertSame(severity::error, $serialized['severity']);
    }

    /**
     * Test that jsonSerialize handles null rule.
     */
    public function test_json_serialize_handles_null_rule(): void {
        $issue = new issue(1, 1, 'Message', null, 'source', severity::warning);
        $serialized = $issue->jsonSerialize();

        $this->assertNull($serialized['rule']);
    }
}
