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

namespace local_devtools\local\debugbar\collectors;

use DebugBar\DataCollector\ConfigCollector;

/**
 * Collector to display Moodle $CFG configuration in the debug bar.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_collector extends ConfigCollector {
    /**
     * Sets the moodle $CFG object as the collector data.
     * @return void
     */
    public function populate() {
        global $CFG;
        $data = (array) clone $CFG;

        $excludedkeys = $this->get_excluded_keys();
        foreach ($excludedkeys as $key) {
            unset($data[$key]);
        }

        $this->setData($data);
    }

    /**
     * Get keys to exclude from display (secrets, passwords, etc.).
     * @return string[]
     */
    private function get_excluded_keys(): array {
        return [
            'dbpassword',
            'facebookapikey',
            'googleoauth2secret',
            'jwtkey',
            'localcachedir',
            'microsoftoauth2clientsecret',
            'proxy',
            'proxyauth',
            'proxypassword',
            'proxyuser',
            'recaptchaprivatekey',
            'recaptchapublickey',
            'recaptchasecret',
            'secret_key',
            'sessioncookie',
            'sessioncookiedomain',
            'sessioncookiepath',
            'sessioncookiesamesite',
            'signingkey',
        ];
    }
}
