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
     * Test that base from_object returns null.
     */
    public function test_from_object_returns_null(): void {
        $obj = (object) ['some' => 'data'];
        $result = issue::from_object($obj);
        $this->assertNull($result);
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
