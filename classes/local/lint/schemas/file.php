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

namespace local_devtools\local\lint\schemas;

use JsonSerializable;
use local_devtools\local\lint\schemas\issue;

/**
 * Class representing a single file with issues.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-type file_data array{file: string, issues: issue[]}
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file implements JsonSerializable {
    /** @var string */
    public readonly string $file;
    /** @var issue[] */
    public array $issues;

    /**
     * Constructor.
     * @param string $file
     * @param issue[] $issues
     */
    public function __construct(string $file, array $issues = []) {
        $this->file = $file;
        $this->issues = $issues;
    }

    /**
     * Adds an issue.
     * @param issue $issue
     * @return self
     */
    public function add_issue(issue $issue): self {
        $this->issues[] = $issue;
        return $this;
    }

    /**
     * Get data to be serialised.
     * @return file_data
     */
    public function jsonSerialize(): array {
        return [
            'file' => $this->file,
            'issues' => $this->issues,
        ];
    }
}
