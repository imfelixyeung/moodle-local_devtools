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
use local_devtools\local\lint\linters\lang;
use local_devtools\local\lint\linters\phpcs;
use local_devtools\local\lint\linters\phplint;
use local_devtools\local\lint\linters\stylelint;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to lint a directory or file.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[AsCommand(name: 'lint:lint', description: 'All linters are enabled by default unless explicitly selected.')]
class lint_lint extends Command {
    /**
     * Invoke
     * @return int
     */
    public function __invoke(
        #[Argument('Directory of file path to lint')] string $path,
        SymfonyStyle $io,
        #[Option('Enable the eslint linter')] bool $eslint = false,
        #[Option('Enable the lang dir linter')] bool $lang = false,
        #[Option('Enable the php-codesniffer linter')] bool $phpcs = false,
        #[Option('Enable the php -l linter')] bool $phplint = false,
        #[Option('Enable the stylelint linter')] bool $stylelint = false,
    ): int {
        global $CFG;
        chdir($CFG->root);

        // If all linter flags are false, then turn all back on.
        if (array_unique([$eslint, $lang, $phpcs, $phplint, $stylelint]) === [false]) {
            $eslint = true;
            $lang = true;
            $phpcs = true;
            $phplint = true;
            $stylelint = true;
        }

        $linters = [
            $eslint ? new eslint() : null,
            $lang ? new lang() : null,
            $phpcs ? new phpcs() : null,
            $phplint ? new phplint() : null,
            $stylelint ? new stylelint() : null,
        ];
        $linters = array_filter($linters, fn($linter) => $linter !== null);

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
