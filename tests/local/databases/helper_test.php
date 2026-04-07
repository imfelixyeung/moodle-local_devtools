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

namespace local_devtools\local\databases;

use advanced_testcase;
use moodle_database;

/**
 * Unit tests for the helper class.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class helper_test extends advanced_testcase {
    /**
     * Test that get_pdo returns null for a non-devtools database object.
     * @covers \local_devtools\local\databases\helper::get_pdo
     */
    public function test_get_pdo_returns_null_for_non_devtools_database(): void {
        $this->resetAfterTest(true);

        $mockdb = $this->createMock(moodle_database::class);
        $result = helper::get_pdo($mockdb);

        $this->assertNull($result);
    }
}
