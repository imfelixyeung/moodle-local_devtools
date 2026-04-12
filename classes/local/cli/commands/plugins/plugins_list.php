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

namespace local_devtools\local\cli\commands\plugins;

use core\plugin_manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to list all installed plugins.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[AsCommand(name: 'plugins:list')]
class plugins_list extends Command {
    /**
     * Invoke
     * @param SymfonyStyle $io
     * @return int
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Option("Outputs json")] bool $json = false,
            // phpcs:ignore moodle.NamingConventions.ValidVariableName.VariableNameLowerCase
        #[Option("Include Moodle standard plugins")] bool $includeStandard = false,
    ): int {
        // phpcs:ignore moodle.NamingConventions.ValidVariableName.VariableNameLowerCase
        $includestandard = $includeStandard;
        $manager = plugin_manager::instance();
        /** @var array<string, array<string, \core\plugininfo\base>> $plugininfo */
        $plugininfo = $manager->get_plugins();

        $results = [];

        foreach ($plugininfo as $type => $plugins) {
            foreach ($plugins as $name => $plugin) {
                $isstandard = $plugin->is_standard();
                if (!$includestandard && $isstandard) {
                    continue;
                }

                $results[] = [
                    'type' => $plugin->type,
                    'name' => $plugin->name,
                    'version' => $plugin->versiondb,
                    'release' => $plugin->release,
                    'component' => $plugin->component,
                    'directory' => $plugin->rootdir,
                    'standard' => $isstandard,
                    'enabled' => $plugin->is_enabled(),
                ];
            }
        }

        if ($json) {
            $jsonstring = json_encode($results);
            if ($jsonstring === false) {
                $io->error("Error serialising JSON.");
                return 1;
            }

            $io->write($jsonstring);
            $io->writeln("");
            return 0;
        }

        $io->table(
            ['Type', 'Name', 'Component', 'Version', 'Release', 'Location'],
            array_filter(
                array_map(
                    fn($result) => [
                        $result['type'],
                        $result['name'],
                        $result['component'],
                        $result['version'],
                        $result['release'],
                        $result['directory'],
                    ],
                    $results
                )
            )
        );

        return 0;
    }
}
