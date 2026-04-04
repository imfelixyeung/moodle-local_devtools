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
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar as BaseDebugBar;

/**
 * Singleton class to manage the debugbar instance and renderer.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debugbar extends BaseDebugBar {
    /** @var self */
    private static ?self $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        $baseurl = new url('/local/devtools/vendor/php-debugbar/php-debugbar/resources');
        $this->getJavascriptRenderer()->setBaseUrl($baseurl->out(false));

        $collectors = [
            PhpInfoCollector::class,
            MessagesCollector::class,
            RequestDataCollector::class,
            TimeDataCollector::class,
            MemoryCollector::class,
            ExceptionsCollector::class,
            PDOCollector::class,
        ];

        foreach ($collectors as $collector) {
            $this->addCollector(new $collector());
        }
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
     * Get the database collector instance, or null if it is not available or of the wrong type.
     */
    public function get_database_collector(): ?PDOCollector {
        $collector = $this->getCollector('pdo');
        if (!($collector instanceof PDOCollector)) {
            // This should never happen but for static analysis we need to check the type before returning.
            return null;
        }
        return $collector;
    }
}
