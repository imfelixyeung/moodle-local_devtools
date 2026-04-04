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
class devtools_database extends \mysqli_native_moodle_database {
    /** @var \mysqli_native_moodle_database|null */
    private \mysqli_native_moodle_database $realdb;

    /**
     * Constructor.
     * @param \mysqli_native_moodle_database $db
     */
    public function __construct(\mysqli_native_moodle_database $db) {
        $this->realdb = $db;
        $this->clone_properties();

        $realdbreflection = new ReflectionClass($this->realdb);

        // Set mysqli variable to null to prevent destructor from closing the connection when this wrapper instance is destroyed.
        if ($realdbreflection->hasProperty('mysqli')) {
            $realdbreflection->getProperty('mysqli')->setValue($this->realdb, null);
        }
    }

    /**
     * Helper method to clone all properties from the real database instance to this wrapper instance.
     */
    private function clone_properties() {
        $realdbreflection = new ReflectionClass($this->realdb);
        $thisdbreflection = new ReflectionClass($this);

        foreach ($realdbreflection->getProperties() as $property) {
            // Skip static properties.
            if ($property->isStatic()) {
                continue;
            }

            $value = $property->getValue($this->realdb);

            if (!$thisdbreflection->hasProperty($property->getName())) {
                // If the wrapper doesn't have the property, we can choose to ignore it or log it.
                // For now, we'll just ignore it.
                continue;
            }

            // Set the same property on this wrapper instance.
            $thisproperty = $thisdbreflection->getProperty($property->getName());
            $thisproperty->setValue($this, $value);
        }
    }
}
