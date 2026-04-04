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

use core\url;
use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;

/**
 * Singleton class to manage the debugbar instance and renderer.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debugbar {
    /** @var StandardDebugBar */
    private StandardDebugBar $debugbar;
    /** @var JavascriptRenderer */
    private JavascriptRenderer $debugbarrenderer;
    /** @var self|null */
    private static ?self $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        require_once(__DIR__ . '/../../vendor/autoload.php');

        $this->debugbar = new StandardDebugBar();
        $this->debugbarrenderer = $this->debugbar->getJavascriptRenderer();
        $baseurl = new url('/local/devtools/vendor/php-debugbar/php-debugbar/resources');
        $this->debugbarrenderer->setBaseUrl($baseurl->out(false));
    }

    /**
     * Get the singleton instance of the debugbar.
     * @return self|null
     */
    public static function instance(): self {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Get the debugbar instance.
     * @return StandardDebugBar
     */
    public function get_debugbar(): StandardDebugBar {
        return $this->debugbar;
    }

    /**
     * Get the debugbar renderer.
     * @return JavascriptRenderer
     */
    public function get_debugbar_renderer(): JavascriptRenderer {
        return $this->debugbarrenderer;
    }
}
