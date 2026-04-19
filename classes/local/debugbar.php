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
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar as BaseDebugBar;
use DebugBar\Storage\SqliteStorage;
use ErrorException;
use local_devtools\local\debugbar\collectors\config_collector;
use local_devtools\local\debugbar\collectors\moodle_collector;
use local_devtools\local\debugbar\collectors\string_manager_collector;
use local_devtools\local\debugbar\log_level;
use Throwable;
use function array_key_exists;

defined('MOODLE_INTERNAL') || die;
require_once(__DIR__ . '/../../vendor/autoload.php');

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
        $this->init_storage();
        $this->init_renderer();

        $editor = \local_devtools\local\config\debugbar::get_editor();
        if ($editor) {
            $this->setEditor($editor);
        }

        $collectors = [
            PhpInfoCollector::class,
            MessagesCollector::class,
            RequestDataCollector::class,
            TimeDataCollector::class,
            MemoryCollector::class,
            ExceptionsCollector::class,
            config_collector::class,
            moodle_collector::class,
            string_manager_collector::class,
        ];

        foreach ($collectors as $collector) {
            $this->addCollector(new $collector());
        }

        if (\local_devtools\local\config\debugbar::is_collect_queries_enabled()) {
            $this->addCollector(new PDOCollector());
        }

        // If the PDO collector is available, set the TimeDataCollector on it so it can log query execution times.
        $pdo = $this->get_database_collector();
        $td = $this->get_time_data_collector();
        if ($pdo && $td) {
            $pdo->setTimeDataCollector($td);
        }

        $this->get_config_collector()?->populate();

        // Configure the message collector to trace messages but ignore this file.
        $message = $this->get_messages_collector();
        $message?->collectFileTrace(true);
        $message?->addBacktraceExcludePaths(['/local/devtools/classes/local/debugbar.php']);

        // Set our own handlers to log errors and exceptions to the debugbar.
        set_error_handler([$this, 'error_handler']);
        set_exception_handler([$this, 'exception_handler']);
    }

    /**
     * Get the singleton instance of the debugbar.
     * @return self
     */
    public static function instance(): self {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Utility class to get a collector with correct type.
     *
     * // phpcs:ignore moodle.Commenting.ValidTags.Invalid
     * @template T of DataCollectorInterface
     * @param string $name
     * @param class-string<T> $class
     * @return T|null
     */
    protected function get_collector(string $name, string $class): ?DataCollectorInterface {
        if (!$this->hasCollector($name)) {
            return null;
        }
        $collector = $this->getCollector($name);
        if (!($collector instanceof $class)) {
            // This should never happen but for static analysis we need to check the type before returning.
            return null;
        }
        return $collector;
    }

    /**
     * Get the database collector instance, or null if it is not available or of the wrong type.
     * @return PDOCollector|null
     */
    public function get_database_collector(): ?PDOCollector {
        return $this->get_collector('pdo', PDOCollector::class);
    }

    /**
     * Get the time data collector instance, or null if it is not available or of the wrong type.
     * @return TimeDataCollector|null
     */
    public function get_time_data_collector(): ?TimeDataCollector {
        return $this->get_collector('time', TimeDataCollector::class);
    }

    /**
     * Get the exceptions collector instance, or null if it is not available or of the wrong type.
     * @return ExceptionsCollector|null
     */
    public function get_exceptions_collector(): ?ExceptionsCollector {
        return $this->get_collector('exceptions', ExceptionsCollector::class);
    }


    /**
     * Get the exceptions collector instance, or null if it is not available or of the wrong type.
     * @return config_collector|null
     */
    public function get_config_collector(): ?config_collector {
        return $this->get_collector('config', config_collector::class);
    }

    /**
     * Get the exceptions collector instance, or null if it is not available or of the wrong type.
     * @return MessagesCollector|null
     */
    public function get_messages_collector(): ?MessagesCollector {
        return $this->get_collector('messages', MessagesCollector::class);
    }

    /**
     * Get the exceptions collector instance, or null if it is not available or of the wrong type.
     * @return string_manager_collector|null
     */
    public function get_string_manager_collector(): ?string_manager_collector {
        return $this->get_collector('string_manager', string_manager_collector::class);
    }

    /**
     * Custom error handler to convert PHP errors to exceptions and log them to the debugbar.
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function error_handler(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Convert the error to an exception and log it to the debugbar.
        $exception = new ErrorException($errstr, 0, $errno, $errfile, $errline);
        $this->log_exception($exception);

        // Now call Moodle's default error handler to log to error log as normal.
        return default_error_handler($errno, $errstr, $errfile, $errline);
    }

    /**
     * Custom exception handler to log uncaught exceptions to the debugbar.
     * @param Throwable $exception
     * @return void
     */
    public function exception_handler(Throwable $exception): void {
        $this->log_exception($exception);

        // Now call Moodle's default exception handler to display the error page and log to error log as normal.
        default_exception_handler($exception);
    }

    /**
     * Logs exception to the debugbar's ExceptionsCollector.
     * @param Throwable $exception The exception to log.
     * @return void
     */
    public function log_exception(Throwable $exception): void {
        $collector = $this->get_exceptions_collector();
        if (!$collector) {
            return;
        }
        $collector->addException($exception);
    }

    /**
     * Logs a message
     * @param mixed $message
     * @param log_level $level
     * @param mixed[] $context
     * @return void
     */
    public static function log(mixed $message, log_level $level = log_level::INFO, array $context = []): void {
        self::instance()->get_messages_collector()?->log($level->value, $message, $context);
    }

    /**
     * Measures execution time and logs it.
     *
     * // phpcs:disable moodle.Commenting.ValidTags
     * @template TReturn
     *
     * @param string $name A descriptive name for the measurement.
     * @param callable():TReturn $callback The callable to be executed.
     * @param bool $logreturn Whether to log the returned callback results.
     * @param float $duration
     * @return TReturn
     * // phpcs:enable
     */
    public static function measure(
        string $name,
        callable $callback,
        bool $logreturn = false,
        ?float &$duration = null
    ) {
        $start = microtime(true);
        $result = $callback();
        $end = microtime(true);
        $duration = $end - $start;

        self::log("Measure: $name took {$duration}s ($start - $end)");

        if ($logreturn) {
            self::log($result);
        }

        self::instance()->get_time_data_collector()?->addMeasure($name, $start, $end);

        return $result;
    }

    /**
     * Initialises the storage for debugbar.
     * @return \DebugBar\Storage\AbstractStorage
     */
    private function init_storage() {
        global $CFG;

        $plugintempdir = "$CFG->tempdir/local_devtools";
        if (!is_dir($plugintempdir)) {
            mkdir($plugintempdir, recursive: true);
        }

        $storage = new SqliteStorage(
            filepath: "$plugintempdir/debugbar.sqlite",
            tableName: 'phpdebugbar',
        );
        $storage->prune(24);
        $this->setStorage($storage);

        return $storage;
    }

    /**
     * Initialises the renderer for debugbar.
     * @return \DebugBar\JavascriptRenderer
     */
    private function init_renderer() {
        $renderer = $this->getJavascriptRenderer();

        $baseurl = new url('/local/devtools/vendor/php-debugbar/php-debugbar/resources');
        $renderer->setBaseUrl($baseurl->out(false));
        $openurl = new url('/local/devtools/debugbar/open.php');
        $renderer->setOpenHandlerUrl($openurl->out(false));

        $renderer->setBindAjaxHandlerToFetch(true);
        $renderer->setBindAjaxHandlerToXHR(true);
        $renderer->setAjaxHandlerEnableTab(true);

        return $renderer;
    }

    /**
     * Store for later use.
     * For example, inspecting a form submit with redirect.
     */
    public function __destruct() {
        // Let's not log the debugbar's open.php.
        if (!array_key_exists('REQUEST_URI', $_SERVER) || !($url = $_SERVER['REQUEST_URI'])) {
            $this->stackData();
            return;
        }

        if (str_contains($url, '/local/devtools/debugbar/open.php')) {
            return;
        }

        $this->stackData();
    }
}
