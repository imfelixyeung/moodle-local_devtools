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

namespace local_devtools\local\lint\linters;

use advanced_testcase;
use local_devtools\local\lint\linters\phplint;
use local_devtools\local\lint\schemas\file;
use local_devtools\local\lint\schemas\issue;
use local_devtools\local\lint\severity;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Unit tests for the base linter class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\linters\base
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class base_test extends advanced_testcase {
    /**
     * Test that get_name returns the short class name.
     */
    public function test_get_name_returns_classname(): void {
        $name = base::get_name();
        $this->assertSame('base', $name);
    }

    /**
     * Test that get_description returns null for base class.
     */
    public function test_get_description_returns_null(): void {
        $description = base::get_description();
        $this->assertNull($description);
    }

    /**
     * Test that get_include_patterns returns empty array for base class.
     */
    public function test_get_include_patterns_returns_empty_array(): void {
        $patterns = base::get_include_patterns();
        $this->assertSame([], $patterns);
    }

    /**
     * Test that get_exclude_patterns returns default exclusion patterns.
     */
    public function test_get_exclude_patterns_returns_default_patterns(): void {
        $patterns = base::get_exclude_patterns();
        $this->assertSame([
            '**/.git/**',
            '**/node_modules/**',
            '**/vendor/**',
        ], $patterns);
    }

    /**
     * Test that can_lint_file returns false when there are no include patterns.
     */
    public function test_can_lint_file_returns_false_when_no_include_patterns(): void {
        $linter = new base();
        $result = $linter->can_lint_file('/some/path/file.php');
        $this->assertFalse($result);
    }

    /**
     * Test that can_lint_file returns true with matching include pattern.
     */
    public function test_can_lint_file_returns_true_with_matching_include(): void {
        $linter = new phplint();
        $result = $linter->can_lint_file('/some/path/file.php');
        $this->assertTrue($result);
    }

    /**
     * Test that can_lint_file returns false when file matches exclude pattern.
     */
    public function test_can_lint_file_returns_false_when_matches_exclude(): void {
        $linter = new phplint();
        $result = $linter->can_lint_file('/project/node_modules/somelib/file.php');
        $this->assertFalse($result);
    }

    /**
     * Test that can_lint_file requires both include match and no exclude match.
     */
    public function test_can_lint_file_requires_both_include_and_not_exclude(): void {
        $linter = new phplint();
        $result = $linter->can_lint_file('/project/node_modules/somelib/file.php');
        $this->assertFalse($result);
    }

    /**
     * Test that flatten_results merges issues for the same file.
     */
    public function test_flatten_results_merges_issues_for_same_file(): void {
        $filepath = '/test/file.php';
        $results = [
            [
                new file($filepath, [
                    new issue(1, 1, 'Error 1', 'rule1', 'source1', severity::error),
                ]),
            ],
            [
                new file($filepath, [
                    new issue(2, 2, 'Error 2', 'rule2', 'source2', severity::warning),
                ]),
            ],
        ];

        $flattened = base::flatten_results($results);

        $this->assertCount(1, $flattened);
        $this->assertSame($filepath, $flattened[0]->file);
        $this->assertCount(2, $flattened[0]->issues);
        $this->assertSame('Error 1', $flattened[0]->issues[0]->message);
        $this->assertSame('Error 2', $flattened[0]->issues[1]->message);
    }

    /**
     * Test that flatten_results removes duplicate files.
     */
    public function test_flatten_results_removes_duplicates(): void {
        $filepath1 = '/test/file1.php';
        $filepath2 = '/test/file2.php';
        $results = [
            [new file($filepath1, [])],
            [new file($filepath2, [])],
            [new file($filepath1, [])],
        ];

        $flattened = base::flatten_results($results);

        $this->assertCount(2, $flattened);
    }

    /**
     * Test that flatten_results returns empty array for empty input.
     */
    public function test_flatten_results_returns_empty_for_empty_input(): void {
        $flattened = base::flatten_results([]);
        $this->assertSame([], $flattened);
    }

    /**
     * Test that set_progress_file does nothing when progress is null.
     */
    public function test_set_progress_file_does_nothing_when_no_progress(): void {
        $this->expectNotToPerformAssertions();
        $linter = new base(null);
        $linter->set_progress_file('/some/path/file.php');
    }

    /**
     * Test that set_progress_file sets message when progress exists.
     */
    public function test_set_progress_file_sets_message_when_progress_exists(): void {
        $this->expectNotToPerformAssertions();
        $output = new NullOutput();
        $progress = new ProgressIndicator($output);
        $linter = new base($progress);
        $linter->set_progress_file('/some/path/file.php');
    }
}
