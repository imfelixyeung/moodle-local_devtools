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

namespace local_devtools\local\api;

use Exception;
use xmldb_file;

/**
 * Plugins API.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class database {
    /**
     * List database tables for a given plugin.
     * @param string $component
     * @return array;
     */
    public static function list_plugin_tables(string $component): array {
        $plugins = plugins::list(true);
        $targetplugin = null;

        foreach ($plugins as $plugin) {
            if ($plugin['component'] !== $component) {
                continue;
            }
            $targetplugin = $plugin;
        }

        if ($targetplugin === null) {
            throw new Exception("Plugin with component '$component' not found.");
        }

        $xmlpath = $targetplugin['directory'] . '/db/install.xml';
        if (!file_exists($xmlpath)) {
            throw new Exception("Plugin does not have a install.xml defined.");
        }

        global $CFG;
        $xml = new xmldb_file($xmlpath);
        $xml->setDTD($CFG->dirroot . '/lib/xmldb/xmldb.dtd');
        $xml->setSchema($CFG->dirroot . '/lib/xmldb/xmldb.xsd');
        $xml->loadXMLStructure();
        $structure = $xml->getStructure();

        /** @var \xmldb_table[] $tables */
        $tables = $structure->getTables();

        $tableresults = [];

        foreach ($tables as $table) {
            $fields = $table->getFields();
            $keys = $table->getKeys();
            $indexes = $table->getIndexes();

            $fieldsresults = [];
            $keysresults = [];
            $indexesresults = [];

            foreach ($fields as $field) {
                $fieldsresults[] = [
                    'name' => $field->getName(),
                    'comment' => $field->getComment(),
                    'type' => self::field_type_to_string($field->getType()),
                ];
            }

            foreach ($keys as $key) {
                $keysresults[] = [
                    'name' => $key->getName(),
                    'comment' => $key->getComment(),
                    'type' => self::key_type_to_string($key->getType()),
                    'fields' => $key->getFields(),
                    'references' => [
                        'table' => $key->getRefTable(),
                        'fields' => $key->getRefFields(),
                    ],
                ];
            }

            foreach ($indexes as $index) {
                $indexesresults[] = [
                    'name' => $index->getName(),
                    'comment' => $index->getComment(),
                    'unique' => $index->getUnique(),
                    'fields' => $index->getFields(),
                ];
            }

            $tableresults[] = [
                'name' => $table->getName(),
                'fields' => $fieldsresults,
                'keys' => $keysresults,
                'indexes' => $indexesresults,
            ];
        }

        $result = [
            'name' => $structure->getName(),
            'comment' => $structure->getComment(),
            'tables' => $tableresults,
        ];

        return $result;
    }

    /**
     * Converts the XMLDB_TYPE_XXX to human readable string.
     * @param int $type
     * @return string
     */
    public static function field_type_to_string(int $type) {
        return match ($type) {
            0 => 'incorrect',
            1 => 'integer',
            2 => 'number',
            3 => 'float',
            4 => 'char',
            5 => 'text',
            6 => 'binary',
            7 => 'datetime',
            8 => 'timestamp',
            default => 'unknown',
        };
    }

    /**
     * Converts the XMLDB_KEY_XXX to human readable string.
     * @param int $type
     * @return string
     */
    public static function key_type_to_string(int $type) {
        return match ($type) {
            0 => 'incorrect',
            1 => 'primary',
            2 => 'unique',
            3 => 'foreign',
            4 => 'check',
            5 => 'foreign_and_unique',
        };
    }
}
