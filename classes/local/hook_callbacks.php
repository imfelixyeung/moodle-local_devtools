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

namespace local_devtools\local;

use core\hook\after_config;
use core\hook\output\before_standard_footer_html_generation;
use core\hook\output\before_standard_head_html_generation;
use local_devtools\local\databases\mysqli_native_devtools_database;

/**
 * Hook callbacks.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Callback for after_config hook.
     * @param after_config $hook
     * @return void
     */
    public static function after_config(
        after_config $hook,
    ): void {
        // Autoload the plugin's vendor dependencies.
        require_once(__DIR__ . '/../../vendor/autoload.php');

        // Add the database PDO connection to the debugbar.
        global $DB;
        $DB = new mysqli_native_devtools_database($DB);
        debugbar::instance()->get_database_collector()->addConnection($DB->get_pdo(), 'moodle');
    }

    /**
     * Callback for before_standard_head_html_generation hook.
     * @param before_standard_head_html_generation $hook
     * @return void
     */
    public static function before_standard_head_html_generation(
        before_standard_head_html_generation $hook,
    ): void {
        $renderer = debugbar::instance()->getJavascriptRenderer();
        $hook->add_html($renderer->renderHead());
    }

    /**
     * Callback for before_standard_footer_html_generation hook.
     * @param before_standard_footer_html_generation $hook
     * @return void
     */
    public static function before_standard_footer_html_generation(
        before_standard_footer_html_generation $hook,
    ): void {
        $renderer = debugbar::instance()->getJavascriptRenderer();
        $hook->add_html($renderer->render());
    }
}
