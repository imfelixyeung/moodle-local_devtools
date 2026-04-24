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

namespace local_devtools\local\lint\linters;

/**
 * The lang dir linter.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lang extends base {
    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            ...['**/lang/*/*.php'],
        ];
    }

    #[\Override]
    public function lint_file(string $filepath): array {
        return [];
    }

    #[\Override]
    public function lint_directory(string $directorypath): array {
        return [];
    }
}
