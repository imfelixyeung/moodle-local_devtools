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

namespace local_devtools\local\cli\commands\database;

use local_devtools\local\api\database;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to list all installed plugins.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[AsCommand(name: 'database:list')]
class database_list extends Command {
    /**
     * Invoke
     * @param string $component
     * @param SymfonyStyle $io
     * @return int
     */
    public function __invoke(
        #[Argument('The component name of the plugin.')] string $component,
        SymfonyStyle $io,
    ): int {
        try {
            $result = database::list_plugin_tables($component);

            $io->writeln(json_encode($result));
            return 0;
        } catch (\Throwable $th) {
            $io->error($th->getMessage());
            return 1;
        }
    }
}
