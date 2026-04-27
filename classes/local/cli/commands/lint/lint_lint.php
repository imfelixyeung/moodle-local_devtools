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

use local_devtools\local\api\linter;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @param string[] $paths
     * @return int
     */
    public function __invoke(
        #[Argument('Paths to lint (must be absolute or relative to the Moodle root)')] array $paths,
        SymfonyStyle $io,
        OutputInterface $output,
        #[Option('Enable the eslint linter')] bool $eslint = false,
        #[Option('Enable the lang dir linter')] bool $lang = false,
        #[Option('Enable the php-codesniffer linter')] bool $phpcs = false,
        #[Option('Enable the php -l linter')] bool $phplint = false,
        #[Option('Enable the phpdoc linter')] bool $phpdoc = false,
        #[Option('Enable the stylelint linter')] bool $stylelint = false,
        #[Option('Output as JSON')] bool $json = false,
        #[Option('Add file:// links to output')] bool $decorate = true,
        #[Option('Enable/disable the progress bar')] bool $progress = true,
    ): int {
        global $CFG;
        chdir($CFG->root);

        $realpaths = [];
        foreach ($paths as $path) {
            $realpath = realpath($path);
            if ($realpath === false) {
                $io->error("Invalid path: $path");
                return -1;
            }
            $realpaths[] = $realpath;
        }

        if ($realpaths === []) {
            $io->error('No paths provided');
            return -1;
        }

        // If all linter flags are false, then turn all back on.
        if (array_unique([$eslint, $lang, $phpcs, $phplint, $phpdoc, $stylelint]) === [false]) {
            $eslint = true;
            $lang = true;
            $phpcs = true;
            $phplint = true;
            $phpdoc = true;
            $stylelint = true;
        }

        $progressindicator = $progress && $output instanceof ConsoleOutputInterface
            ? new ProgressIndicator($output->getErrorOutput())
            : null;

        $linters = linter::get_linters_classnames(
            eslint: $eslint,
            lang: $lang,
            phpcs: $phpcs,
            phplint: $phplint,
            phpdoc: $phpdoc,
            stylelint: $stylelint
        );

        $results = linter::run($realpaths, $linters, progress: $progressindicator);

        if ($json) {
            $jsonstring = json_encode([
                'linters' => linter::get_linters_info($linters),
                'files' => $results,
            ]);
            if ($jsonstring === false) {
                $io->error('Error encoding linter results JSON');
                return -1;
            }
            $io->writeln($jsonstring);
            return 0;
        }

        $decorateoutput = $decorate && $io->isDecorated();

        foreach ($results as $fileresult) {
            $path = $fileresult->file;
            $issues = $fileresult->issues;

            foreach ($issues as $issue) {
                $severity = $issue->severity->value;
                $message = $issue->message;
                $rule = "$issue->source/$issue->rule";
                $filelink = $fileresult->format_path($issue->line, $issue->column, $decorateoutput);
                $out = "$filelink: $severity: $message ($rule)";
                $io->writeln($out);
            }
        }

        return 0;
    }
}
