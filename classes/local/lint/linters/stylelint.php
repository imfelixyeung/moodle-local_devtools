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

namespace local_devtools\local\lint\linters;

use local_devtools\local\lint\issue;
use Symfony\Component\Process\Process;

/**
 * The stylelint linter.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stylelint extends base {
    #[\Override]
    public function lint_file(string $filepath): array {
        $filepath = realpath($filepath);
        if ($filepath === false) {
            return [];
        }

        $process = new Process(['bunx', 'stylelint', '--formatter', 'json', $filepath]);
        $process->run();

        $output = $process->getOutput();
        echo $output;
        $jsonoutput = json_decode($output);
        if ($jsonoutput === null) {
            return [];
        }

        $result = [];

        foreach ($jsonoutput as $lintedfile) {
            $issues = [];
            $warnings = $lintedfile->warnings;
            foreach ($warnings as $warning) {
                $issues[] = issue::from_stylelint_warning($warning);
            }

            $result = [
                'file' => $lintedfile->source,
                'issues' => $issues,
            ];
        }

        return $result;
    }
}
