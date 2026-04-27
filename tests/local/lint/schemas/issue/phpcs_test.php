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
 * Unit tests for the phpcs issue class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\schemas\issue\phpcs
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class phpcs_test extends advanced_testcase {
    /**
     * Test that from_object creates issue from phpcs message.
     */
    public function test_from_object_creates_issue(): void {
        $obj = (object) [
            'line' => 10,
            'column' => 15,
            'source' => 'Generic.WhiteSpace.ScopeIndent',
            'severity' => 5,
            'message' => 'Expected 4 spaces before',
        ];

        $issue = phpcs::from_object($obj);

        $this->assertNotNull($issue);
        $this->assertSame(10, $issue->line);
        $this->assertSame(15, $issue->column);
        $this->assertSame('Expected 4 spaces before', $issue->message);
        $this->assertSame('Generic.WhiteSpace.ScopeIndent', $issue->rule);
        $this->assertSame('phpcs', $issue->source);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_object maps severity <= 0 to info.
     */
    public function test_from_object_maps_zero_or_less_to_info(): void {
        $obj = (object) [
            'line' => 1,
            'column' => 1,
            'source' => 'source',
            'severity' => 0,
            'message' => 'Message',
        ];

        $issue = phpcs::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::info, $issue->severity);
    }

    /**
     * Test that from_object maps severity 1-4 to warning.
     */
    public function test_from_object_maps_one_to_four_to_warning(): void {
        $obj1 = (object) [
            'line' => 1,
            'column' => 1,
            'source' => 'source',
            'severity' => 1,
            'message' => 'Message',
        ];
        $obj4 = (object) [
            'line' => 1,
            'column' => 1,
            'source' => 'source',
            'severity' => 4,
            'message' => 'Message',
        ];

        $issue1 = phpcs::from_object($obj1);
        $issue4 = phpcs::from_object($obj4);

        $this->assertNotNull($issue1);
        $this->assertNotNull($issue4);
        $this->assertSame(severity::warning, $issue1->severity);
        $this->assertSame(severity::warning, $issue4->severity);
    }

    /**
     * Test that from_object maps severity 5 to error.
     */
    public function test_from_object_maps_five_to_error(): void {
        $obj = (object) [
            'line' => 1,
            'column' => 1,
            'source' => 'source',
            'severity' => 5,
            'message' => 'Message',
        ];

        $issue = phpcs::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::error, $issue->severity);
    }

    /**
     * Test that from_object maps unknown severity to unknown.
     */
    public function test_from_object_maps_unknown_to_unknown(): void {
        $obj = (object) [
            'line' => 1,
            'column' => 1,
            'source' => 'source',
            'severity' => 99,
            'message' => 'Message',
        ];

        $issue = phpcs::from_object($obj);
        $this->assertNotNull($issue);
        $this->assertSame(severity::unknown, $issue->severity);
    }

    /**
     * Test that from_object returns null when empty object is passed.
     */
    public function test_from_object_handles_empty_object(): void {
        $obj = (object) [];
        $issue = phpcs::from_object($obj);
        $this->assertNull($issue);
    }
}
