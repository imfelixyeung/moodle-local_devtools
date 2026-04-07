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

namespace local_devtools\local\data;

/**
 * Holds list of supported code editors.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editor {
    /**
     * List of supported code editors.
     * @var array{id: string, name: string}[]
     */
    protected static array $editors = [
        ['id' => 'sublime', 'name' => 'Sublime Text'],
        ['id' => 'textmate', 'name' => 'TextMate'],
        ['id' => 'emacs', 'name' => 'Emacs'],
        ['id' => 'macvim', 'name' => 'MacVim'],
        ['id' => 'codelite', 'name' => 'CodeLite'],
        ['id' => 'phpstorm', 'name' => 'PHPStorm'],
        ['id' => 'phpstorm-remote', 'name' => 'PHPStorm (Remote)'],
        ['id' => 'idea', 'name' => 'IntelliJ IDEA'],
        ['id' => 'idea-remote', 'name' => 'IntelliJ IDEA (Remote)'],
        ['id' => 'vscode', 'name' => 'VS Code'],
        ['id' => 'vscode-insiders', 'name' => 'VS Code Insiders'],
        ['id' => 'vscode-remote', 'name' => 'VS Code (Remote)'],
        ['id' => 'vscode-insiders-remote', 'name' => 'VS Code Insiders (Remote)'],
        ['id' => 'vscodium', 'name' => 'VSCodium'],
        ['id' => 'nova', 'name' => 'Nova'],
        ['id' => 'xdebug', 'name' => 'Xdebug'],
        ['id' => 'atom', 'name' => 'Atom'],
        ['id' => 'espresso', 'name' => 'Espresso'],
        ['id' => 'netbeans', 'name' => 'NetBeans'],
        ['id' => 'cursor', 'name' => 'Cursor'],
        ['id' => 'cursor-remote', 'name' => '(Remote) Cursor'],
        ['id' => 'windsurf', 'name' => "Windsurf"],
        ['id' => 'zed', 'name' => 'Zed'],
        ['id' => 'antigravity', 'name' => 'Antigravity'],
    ];

    /**
     * Get the list of supported editors.
     * @return array{id: string, name: string}[]
     */
    public static function get(): array {
        return self::$editors;
    }

    /**
     * Get the list of supported editors formatted for a select menu (id => name).
     * @return array<string, string>
     */
    public static function get_menu(): array {
        $menu = ['' => get_string('none')];
        foreach (self::$editors as $editor) {
            $menu[$editor['id']] = $editor['name'];
        }
        return $menu;
    }
}
