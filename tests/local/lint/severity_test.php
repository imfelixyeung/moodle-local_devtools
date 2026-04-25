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

namespace local_devtools\local\lint;

use advanced_testcase;

/**
 * Unit tests for the severity enum.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\lint\severity
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class severity_test extends advanced_testcase {
    /**
     * Test that from_eslint maps severity 0 to info.
     */
    public function test_from_eslint_maps_0_to_info(): void {
        $result = severity::from_eslint(0);
        $this->assertSame(severity::info, $result);
    }

    /**
     * Test that from_eslint maps severity 1 to warning.
     */
    public function test_from_eslint_maps_1_to_warning(): void {
        $result = severity::from_eslint(1);
        $this->assertSame(severity::warning, $result);
    }

    /**
     * Test that from_eslint maps severity 2 to error.
     */
    public function test_from_eslint_maps_2_to_error(): void {
        $result = severity::from_eslint(2);
        $this->assertSame(severity::error, $result);
    }

    /**
     * Test that from_eslint maps unknown values to unknown.
     */
    public function test_from_eslint_maps_unknown_to_unknown(): void {
        $result = severity::from_eslint(99);
        $this->assertSame(severity::unknown, $result);
    }

    /**
     * Test that from_stylelint maps 'warning' to warning.
     */
    public function test_from_stylelint_maps_warning_string_to_warning(): void {
        $result = severity::from_stylelint('warning');
        $this->assertSame(severity::warning, $result);
    }

    /**
     * Test that from_stylelint maps 'error' to error.
     */
    public function test_from_stylelint_maps_error_string_to_error(): void {
        $result = severity::from_stylelint('error');
        $this->assertSame(severity::error, $result);
    }

    /**
     * Test that from_stylelint maps unknown strings to unknown.
     */
    public function test_from_stylelint_maps_unknown_to_unknown(): void {
        $result = severity::from_stylelint('invalid');
        $this->assertSame(severity::unknown, $result);
    }

    /**
     * Test that from_phpcs maps severity <= 0 to info.
     */
    public function test_from_phpcs_maps_zero_or_less_to_info(): void {
        $result = severity::from_phpcs(0);
        $this->assertSame(severity::info, $result);
        $result = severity::from_phpcs(-1);
        $this->assertSame(severity::info, $result);
    }

    /**
     * Test that from_phpcs maps severity <= 4 to warning.
     */
    public function test_from_phpcs_maps_one_to_four_to_warning(): void {
        $result = severity::from_phpcs(1);
        $this->assertSame(severity::warning, $result);
        $result = severity::from_phpcs(4);
        $this->assertSame(severity::warning, $result);
    }

    /**
     * Test that from_phpcs maps severity 5 to error.
     */
    public function test_from_phpcs_maps_five_to_error(): void {
        $result = severity::from_phpcs(5);
        $this->assertSame(severity::error, $result);
    }

    /**
     * Test that from_phpcs maps unknown values to unknown.
     */
    public function test_from_phpcs_maps_unknown_to_unknown(): void {
        $result = severity::from_phpcs(99);
        $this->assertSame(severity::unknown, $result);
    }
}
