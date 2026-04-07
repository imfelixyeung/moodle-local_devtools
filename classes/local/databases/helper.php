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
use moodle_database;
use mysqli_native_moodle_database;

/**
 * Helper functions for interacting with devtools databases.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Wrap the provided database instance with the appropriate devtools database wrapper, if not already wrapped.
     * @param moodle_database $db
     * @return moodle_database
     */
    public static function wrap_database(moodle_database $db): moodle_database {
        if ($db instanceof mariadb_native_moodle_database) {
            return mariadb_native_devtools_database::wrap($db);
        }

        if ($db instanceof mysqli_native_moodle_database) {
            return mysqli_native_devtools_database::wrap($db);
        }

        // For other database types, we can add more wrappers in the future if needed.
        return $db;
    }

    /**
     * Get the PDO instance from a given database, if it's a devtools wrapper.
     * @param moodle_database $db
     */
    public static function get_pdo(moodle_database $db): ?TraceablePDO {
        if (!($db instanceof devtools_database_interface)) {
            return null;
        }

        return $db->get_pdo();
    }
}
