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

namespace local_devtools\local\databases;

use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\PDO\TracedStatement;
use mysqli_native_moodle_database;
use PDO;
use ReflectionClass;
use function in_array;
use function is_array;

/**
 * Singleton class to manage the debugbar instance and renderer.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mysqli_native_devtools_database extends mysqli_native_moodle_database {
    /** @var TraceablePDO */
    private TraceablePDO $pdo;
    /** @var (TracedStatement|null)[] */
    private array $executedstatements = [];

    /**
     * Constructor.
     * @param mysqli_native_moodle_database $db
     */
    public function __construct(mysqli_native_moodle_database $db) {
        $this->pdo = new TraceablePDO(
            new PDO("mysql:host={$db->dbhost};dbname={$db->dbname}", $db->dbuser, $db->dbpass)
        );

        $this->clone_connection($db);
    }

    /**
     * Clone the database connection details from the original database instance.
     * @param mysqli_native_moodle_database $db
     * @return void
     */
    protected function clone_connection(mysqli_native_moodle_database $db) {
        $reflection = new ReflectionClass($db);

        $dbhost = (string) $reflection->getProperty('dbhost')->getValue($db);
        $dbuser = (string) $reflection->getProperty('dbuser')->getValue($db);
        $dbpass = (string) $reflection->getProperty('dbpass')->getValue($db);
        $dbname = (string) $reflection->getProperty('dbname')->getValue($db);
        $prefix = (string) $reflection->getProperty('prefix')->getValue($db);
        $dboptions = $reflection->getProperty('dboptions')->getValue($db);
        $dboptions = is_array($dboptions) ? $dboptions : null;

        $this->connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);
    }

    /**
     * Determine whether a query of the given type should be logged.
     * @param int $type
     * @return bool
     */
    protected function should_log_query(int $type): bool {
        // Only log application queries, not internal ones.
        /** @var int[] $committypes */
        static $committypes = [SQL_QUERY_SELECT, SQL_QUERY_INSERT, SQL_QUERY_UPDATE];
        return in_array($type, $committypes, true);
    }

    // phpcs:ignore moodle.Commenting.InlineComment
    // @phpstan-ignore missingType.iterableValue
    #[\Override]
    protected function query_start($sql, ?array $params, $type, $extrainfo = null) {
        if (!$this->should_log_query($type)) {
            parent::query_start($sql, $params, $type, $extrainfo);
            $this->executedstatements[] = null; // Placeholder to keep the stack in sync.
            return;
        }

        $statement = new TracedStatement($sql, $params ?? []);
        $statement->start();
        $this->executedstatements[] = $statement;
        $this->pdo->addExecutedStatement($statement);

        parent::query_start($sql, $params, $type, $extrainfo);
    }

    #[\Override]
    protected function query_end($result) {
        parent::query_end($result);

        $statement = array_pop($this->executedstatements);
        if (!$statement) {
            return;
        }

        /** @var \mysqli_result|null $mysqliresult */
        $mysqliresult = $result instanceof \mysqli_result ? $result : null;

        if ($mysqliresult) {
            $statement->end(rowCount: (int) $mysqliresult->num_rows);
        } else {
            $statement->end();
        }
    }

    #[\Override]
    protected function begin_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $this->pdo->beginTransaction();
        parent::begin_transaction();
    }

    #[\Override]
    protected function commit_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $this->pdo->commit();
        parent::commit_transaction();
    }

    #[\Override]
    protected function rollback_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $this->pdo->rollBack();
        parent::rollback_transaction();
    }

    /**
     * Get the TraceablePDO instance.
     * @return TraceablePDO
     */
    public function get_pdo(): TraceablePDO {
        return $this->pdo;
    }
}
