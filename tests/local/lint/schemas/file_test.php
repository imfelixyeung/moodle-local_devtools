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
 * Unit tests for the file class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\schemas\file
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class file_test extends advanced_testcase {
    /**
     * Test that constructor sets file path.
     */
    public function test_constructor_sets_file_path(): void {
        $file = new file('/path/to/file.php');
        $this->assertSame('/path/to/file.php', $file->file);
    }

    /**
     * Test that constructor initializes empty issues array.
     */
    public function test_constructor_initializes_empty_issues(): void {
        $file = new file('/path/to/file.php');
        $this->assertSame([], $file->issues);
    }

    /**
     * Test that constructor accepts initial issues.
     */
    public function test_constructor_accepts_initial_issues(): void {
        $issue1 = new issue(1, 1, 'Error 1', 'rule1', 'source1', severity::error);
        $issue2 = new issue(2, 2, 'Error 2', 'rule2', 'source2', severity::warning);
        $file = new file('/path/to/file.php', [$issue1, $issue2]);

        $this->assertCount(2, $file->issues);
        $this->assertSame('Error 1', $file->issues[0]->message);
        $this->assertSame('Error 2', $file->issues[1]->message);
    }

    /**
     * Test that add_issue adds issue to array.
     */
    public function test_add_issue_adds_issue_to_array(): void {
        $file = new file('/path/to/file.php');
        $issue = new issue(1, 1, 'Test error', 'rule1', 'source1', severity::error);

        $file->add_issue($issue);

        $this->assertCount(1, $file->issues);
        $this->assertSame('Test error', $file->issues[0]->message);
    }

    /**
     * Test that add_issue returns self for fluent interface.
     */
    public function test_add_issue_returns_self(): void {
        $file = new file('/path/to/file.php');
        $issue = new issue(1, 1, 'Error', 'rule', 'source', severity::error);

        $result = $file->add_issue($issue);

        $this->assertSame($file, $result);
    }

    /**
     * Test that jsonSerialize returns correct structure.
     */
    public function test_json_serialize_returns_correct_structure(): void {
        $issue = new issue(1, 1, 'Error message', 'rule1', 'source1', severity::error);
        $file = new file('/path/to/file.php', [$issue]);
        $serialized = $file->jsonSerialize();

        $this->assertArrayHasKey('file', $serialized);
        $this->assertArrayHasKey('issues', $serialized);
        $this->assertSame('/path/to/file.php', $serialized['file']);
        $this->assertCount(1, $serialized['issues']);
    }
}
