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
use mariadb_native_moodle_database;
use PDO;

/**
 * MariaDB Moodle database wrapper.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mariadb_native_devtools_database extends mariadb_native_moodle_database implements devtools_database_interface {
    use devtools_database_trait;

    /**
     * Constructor.
     * @param mariadb_native_moodle_database $db
     */
    protected function __construct(mariadb_native_moodle_database $db) {
        $this->pdo = new TraceablePDO(
            new PDO("mysql:host={$db->dbhost};dbname={$db->dbname}", $db->dbuser, $db->dbpass)
        );

        $this->clone_connection($db);
    }

    /**
     * Wrap the provided database instance with the devtools database class, if not already wrapped.
     * @param mariadb_native_moodle_database $db
     * @return self
     */
    public static function wrap(mariadb_native_moodle_database $db): self {
        if ($db instanceof self) {
            return $db;
        }
        return new self($db);
    }
}
