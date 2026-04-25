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

namespace local_devtools\local;

use advanced_testcase;

/**
 * Unit tests for the utils class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\utils
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utils_test extends advanced_testcase {
    /**
     * Test that array_filter_left returns all items when all match.
     */
    public function test_array_filter_left_returns_all_items_when_all_match(): void {
        $array = [1, 2, 3];
        $result = utils::array_filter_left($array, fn($item) => $item > 0);

        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * Test that array_filter_left stops when callback returns false.
     */
    public function test_array_filter_left_stops_on_first_false(): void {
        $array = [1, 2, 3, 4, 5];
        $result = utils::array_filter_left($array, fn($item) => $item < 3);

        $this->assertSame([1, 2], $result);
    }

    /**
     * Test that array_filter_left returns empty when first item fails.
     */
    public function test_array_filter_left_returns_empty_when_first_fails(): void {
        $array = [1, 2, 3];
        $result = utils::array_filter_left($array, fn($item) => $item > 1);

        $this->assertSame([], $result);
    }

    /**
     * Test that array_filter_left works with strings.
     */
    public function test_array_filter_left_works_with_strings(): void {
        $array = ['a', 'b', 'c', 'stop', 'd'];
        $result = utils::array_filter_left($array, fn($item) => $item !== 'stop');

        $this->assertSame(['a', 'b', 'c'], $result);
    }

    /**
     * Test that array_filter_left handles empty array.
     */
    public function test_array_filter_left_handles_empty_array(): void {
        $array = [];
        $result = utils::array_filter_left($array, fn($item) => true);

        $this->assertSame([], $result);
    }
}
