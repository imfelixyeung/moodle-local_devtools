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

namespace local_devtools\local\cli\commands\lint;

use local_devtools\local\lint\linters\base;
use local_devtools\local\lint\linters\eslint;
use local_devtools\local\lint\linters\phplint;
use local_devtools\local\lint\linters\stylelint;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to lint a directory or file.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[AsCommand(name: 'lint:lint')]
class lint_lint extends Command {
    /**
     * Invoke
     * @return int
     */
    public function __invoke(
        #[Argument('Directory of file path to lint')] string $path,
        SymfonyStyle $io
    ): int {
        global $CFG;
        chdir($CFG->root);

        $linters = [
            new eslint(),
            new stylelint(),
            new phplint(),
        ];

        $results = array_map(fn(base $linter) => $linter->lint($path), $linters);

        $json = json_encode(base::flatten_results($results));
        if ($json === false) {
            $io->error('Error encoding linter results JSON');
            return -1;
        }
        $io->writeln($json);
        return 0;
    }
}
