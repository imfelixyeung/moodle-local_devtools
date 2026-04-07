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

namespace local_devtools\local\data;

use advanced_testcase;

/**
 * Unit tests for the editor class.
 *
 * @package   local_devtools
 * @covers    \local_devtools\local\data\editor
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class editor_test extends advanced_testcase {
    /**
     * Test that get() returns a non-empty array of editors.
     */
    public function test_get_returns_non_empty_array(): void {
        $editors = editor::get();
        $this->assertNotEmpty($editors);
    }

    /**
     * Test that get() returns an array with the expected structure.
     */
    public function test_get_returns_expected_structure(): void {
        $editors = editor::get();
        $first = reset($editors);

        $this->assertIsArray($first);
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
    }

    /**
     * Test that get() contains expected editors.
     */
    public function test_get_contains_expected_editors(): void {
        $editors = editor::get();
        $editorids = array_column($editors, 'id');

        $this->assertContains('vscode', $editorids);
        $this->assertContains('phpstorm', $editorids);
        $this->assertContains('sublime', $editorids);
    }

    /**
     * Test that get_menu() returns a non-empty array.
     */
    public function test_get_menu_returns_non_empty_array(): void {
        $this->resetAfterTest(true);

        $menu = editor::get_menu();
        $this->assertNotEmpty($menu);
    }

    /**
     * Test that get_menu() has empty string as first key.
     */
    public function test_get_menu_first_key_is_empty(): void {
        $this->resetAfterTest(true);

        $menu = editor::get_menu();
        $this->assertArrayHasKey('', $menu);
    }

    /**
     * Test that get_menu() contains expected editors.
     */
    public function test_get_menu_contains_expected_editors(): void {
        $this->resetAfterTest(true);

        $menu = editor::get_menu();

        $this->assertArrayHasKey('vscode', $menu);
        $this->assertArrayHasKey('phpstorm', $menu);
        $this->assertArrayHasKey('sublime', $menu);
    }

    /**
     * Test that get_menu() values are strings.
     */
    public function test_get_menu_values_are_strings(): void {
        $this->resetAfterTest(true);

        $menu = editor::get_menu();
        foreach ($menu as $value) {
            $this->assertIsString($value);
        }
    }
}
