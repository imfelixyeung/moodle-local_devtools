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

use ReflectionClass;

/**
 * Singleton class to manage the debugbar instance and renderer.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\AllowDynamicProperties]
class devtools_database extends \moodle_database {
    /** @var \moodle_database|null */
    private \moodle_database $realdb;
    /** @var ReflectionClass */
    private ReflectionClass $realdbreflection;

    /**
     * Constructor.
     * @param \moodle_database $db
     */
    public function __construct(\moodle_database $db) {
        $this->realdb = $db;
        $this->realdbreflection = new ReflectionClass($db);

        $this->clone_properties();
        $this->temptables = new \moodle_temptables($this);
    }

    /**
     * Helper method to invoke protected methods of the real database instance using reflection.
     * @param string $methodname
     * @param mixed[] $args
     */
    private function reflection_invoke(string $methodname, array $args = []) {
        $this->clone_properties();

        $method = $this->realdbreflection->getMethod($methodname);
        $result = $method->invokeArgs($this->realdb, $args);

        $this->clone_properties();
        return $result;
    }

    /**
     * Helper method to clone all properties from the real database instance to this wrapper instance.
     */
    private function clone_properties() {
        foreach ($this->realdbreflection->getProperties() as $property) {
            $value = $property->getValue($this->realdb);
            $this->$property = $value;
        }
    }


    #[\Override] public function driver_installed() {
        return $this->reflection_invoke('driver_installed', func_get_args());
    }

    #[\Override]
    public function get_dbfamily() {
        return $this->reflection_invoke('get_dbfamily', func_get_args());
    }

    #[\Override]
    protected function get_dbtype() {
        return $this->reflection_invoke('get_dbtype', func_get_args());
    }

    #[\Override]
    protected function get_dblibrary() {
        return $this->reflection_invoke('get_dblibrary', func_get_args());
    }

    #[\Override]
    public function get_name() {
        return $this->reflection_invoke('get_name', func_get_args());
    }

    #[\Override]
    public function get_configuration_help() {
        return $this->reflection_invoke('get_configuration_help', func_get_args());
    }

    #[\Override]
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, ?array $dboptions = null) {
        return $this->reflection_invoke('connect', func_get_args());
    }

    #[\Override]
    public function get_server_info() {
        return $this->reflection_invoke('get_server_info', func_get_args());
    }

    #[\Override]
    protected function allowed_param_types() {
        return $this->reflection_invoke('allowed_param_types', func_get_args());
    }

    #[\Override]
    public function get_last_error() {
        return $this->reflection_invoke('get_last_error', func_get_args());
    }

    #[\Override]
    public function get_tables($usecache = true) {
        return $this->reflection_invoke('get_tables', func_get_args());
    }

    #[\Override]
    public function get_indexes($table) {
        return $this->reflection_invoke('get_indexes', func_get_args());
    }

    #[\Override]
    protected function fetch_columns(string $table): array {
        return $this->reflection_invoke('fetch_columns', func_get_args());
    }

    #[\Override]
    protected function normalise_value($column, $value) {
        return $this->reflection_invoke('normalise_value', func_get_args());
    }

    #[\Override]
    public function change_database_structure($sql, $tablenames = null) {
        return $this->reflection_invoke('change_database_structure', func_get_args());
    }

    #[\Override]
    public function execute($sql, ?array $params = null) {
        return $this->reflection_invoke('execute', func_get_args());
    }

    #[\Override]
    public function get_recordset_sql($sql, ?array $params = null, $limitfrom = 0, $limitnum = 0) {
        return $this->reflection_invoke('get_recordset_sql', func_get_args());
    }

    #[\Override]
    public function get_records_sql($sql, ?array $params = null, $limitfrom = 0, $limitnum = 0) {
        return $this->reflection_invoke('get_records_sql', func_get_args());
    }

    #[\Override]
    public function get_fieldset_sql($sql, ?array $params = null) {
        return $this->reflection_invoke('get_fieldset_sql', func_get_args());
    }

    #[\Override]
    public function insert_record_raw($table, $params, $returnid = true, $bulk = false, $customsequence = false) {
        return $this->reflection_invoke('insert_record_raw', func_get_args());
    }

    #[\Override]
    public function insert_record($table, $dataobject, $returnid = true, $bulk = false) {
        return $this->reflection_invoke('insert_record', func_get_args());
    }

    #[\Override]
    public function import_record($table, $dataobject) {
        return $this->reflection_invoke('import_record', func_get_args());
    }

    #[\Override]
    public function update_record_raw($table, $params, $bulk = false) {
        return $this->reflection_invoke('update_record_raw', func_get_args());
    }

    #[\Override]
    public function update_record($table, $dataobject, $bulk = false) {
        return $this->reflection_invoke('update_record', func_get_args());
    }

    #[\Override]
    public function set_field_select($table, $newfield, $newvalue, $select, ?array $params = null) {
        return $this->reflection_invoke('set_field_select', func_get_args());
    }

    #[\Override]
    public function delete_records_select($table, $select, ?array $params = null) {
        return $this->reflection_invoke('delete_records_select', func_get_args());
    }

    #[\Override]
    public function sql_concat(...$arr) {
        return $this->reflection_invoke('sql_concat', func_get_args());
    }

    #[\Override]
    public function sql_concat_join($separator = "' '", $elements = []) {
        return $this->reflection_invoke('sql_concat_join', func_get_args());
    }

    #[\Override]
    public function sql_group_concat(string $field, string $separator = ', ', string $sort = ''): string {
        return $this->reflection_invoke('sql_group_concat', func_get_args());
    }

    #[\Override]
    protected function begin_transaction() {
        return $this->reflection_invoke('begin_transaction', func_get_args());
    }

    #[\Override]
    protected function commit_transaction() {
        return $this->reflection_invoke('commit_transaction', func_get_args());
    }

    #[\Override]
    protected function rollback_transaction() {
        return $this->reflection_invoke('rollback_transaction', func_get_args());
    }

    /**
     * Magic method to catch calls to any methods not explicitly overridden
     * and delegate them to the real database instance using reflection.
     * @param mixed $method
     * @param mixed $args
     */
    public function __call($method, $args) {
        return $this->reflection_invoke($method, $args);
    }

    /**
     * Magic method to catch access to any properties not explicitly defined and delegate them to the real database instance.
     * @param mixed $name
     */
    public function __get($name) {
        return $this->realdb->$name;
    }
}
