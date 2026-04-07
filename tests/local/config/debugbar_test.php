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

namespace local_devtools\local\config;

use advanced_testcase;

/**
 * Unit tests for the debugbar config class.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class debugbar_test extends advanced_testcase {
    /**
     * Test that is_enabled returns true when config is set to 1.
     * @covers \local_devtools\local\config::is_enabled()
     */
    public function test_is_enabled_returns_true_when_enabled(): void {
        $this->resetAfterTest(true);
        set_config('debugbar_enabled', '1', 'local_devtools');

        $this->assertTrue(debugbar::is_enabled());
    }

    /**
     * Test that is_enabled returns false when config is not set.
     * @covers \local_devtools\local\config::is_enabled()
     */
    public function test_is_enabled_returns_false_when_not_set(): void {
        $this->resetAfterTest(true);

        $this->assertFalse(debugbar::is_enabled());
    }

    /**
     * Test that is_enabled returns false when config is set to 0.
     * @covers \local_devtools\local\config::is_enabled()
     */
    public function test_is_enabled_returns_false_when_disabled(): void {
        $this->resetAfterTest(true);
        set_config('debugbar_enabled', '0', 'local_devtools');

        $this->assertFalse(debugbar::is_enabled());
    }

    /**
     * Test that is_collect_queries_enabled returns true when config is set to 1.
     * @covers \local_devtools\local\config::is_collect_queries_enabled()
     */
    public function test_is_collect_queries_enabled_returns_true_when_enabled(): void {
        $this->resetAfterTest(true);
        set_config('debugbar_collect_queries', '1', 'local_devtools');

        $this->assertTrue(debugbar::is_collect_queries_enabled());
    }

    /**
     * Test that is_collect_queries_enabled returns false when config is not set.
     * @covers \local_devtools\local\config::is_collect_queries_enabled()
     */
    public function test_is_collect_queries_enabled_returns_false_when_not_set(): void {
        $this->resetAfterTest(true);

        $this->assertFalse(debugbar::is_collect_queries_enabled());
    }

    /**
     * Test that get_editor returns the configured editor.
     * @covers \local_devtools\local\config::get_editor()
     */
    public function test_get_editor_returns_configured_editor(): void {
        $this->resetAfterTest(true);
        set_config('debugbar_editor', 'phpstorm', 'local_devtools');

        $this->assertSame('phpstorm', debugbar::get_editor());
    }

    /**
     * Test that get_editor returns null when not configured.
     * @covers \local_devtools\local\config::get_editor()
     */
    public function test_get_editor_returns_null_when_not_set(): void {
        $this->resetAfterTest(true);

        $this->assertNull(debugbar::get_editor());
    }

    /**
     * Test that get_editor returns null when config is empty string.
     * @covers \local_devtools\local\config::get_editor()
     */
    public function test_get_editor_returns_null_when_empty_string(): void {
        $this->resetAfterTest(true);
        set_config('debugbar_editor', '', 'local_devtools');

        $this->assertNull(debugbar::get_editor());
    }
}
