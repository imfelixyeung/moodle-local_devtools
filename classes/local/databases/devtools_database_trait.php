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
use moodle_database;
use mysqli_result;
use ReflectionClass;
use function in_array;
use function is_array;

/**
 * Common wrapper functions.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait devtools_database_trait {
    /** @var TraceablePDO */
    private TraceablePDO $pdo;
    /** @var (TracedStatement|null)[] */
    private array $executedstatements = [];

    /**
     * Clone the database connection details from the original database instance.
     * @param moodle_database $db
     * @return void
     */
    protected function clone_connection(moodle_database $db) {
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

    /**
     * Start query wrapper.
     * @param mixed $sql
     * @param array|null $params
     * @param mixed $type
     * @param mixed $extrainfo
     * @return void
     */
    // phpcs:disable moodle.Commenting.MissingDocblock.Function
    // phpcs:ignore moodle.Commenting.InlineComment
    // @phpstan-ignore missingType.iterableValue
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
    // phpcs:enable moodle.Commenting.MissingDocblock.Function

    /**
     * End query wrapper.
     * @param mysqli_result|null $result
     * @return void
     */
    protected function query_end($result) {
        parent::query_end($result);

        $statement = array_pop($this->executedstatements);
        if (!$statement) {
            return;
        }

        $mysqliresult = $result instanceof mysqli_result ? $result : null;

        if ($mysqliresult) {
            $statement->end(rowCount: (int) $mysqliresult->num_rows);
        } else {
            $statement->end();
        }
    }

    /**
     * Begin transaction wrapper.
     * @return void
     */
    protected function begin_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $this->pdo->beginTransaction();
        parent::begin_transaction();
    }

    /**
     * Commit transaction wrapper.
     * @return void
     */
    protected function commit_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $this->pdo->commit();
        parent::commit_transaction();
    }

    /**
     * Rollback transaction wrapper.
     * @return void
     */
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
