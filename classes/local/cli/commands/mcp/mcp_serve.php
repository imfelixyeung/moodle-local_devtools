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

namespace local_devtools\local\cli\commands\mcp;

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Command to start the MCP server.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[AsCommand(name: 'mcp:serve')]
class mcp_serve extends Command {
    /**
     * Invoke
     * @return int
     */
    public function __invoke(): int {
        // Build and run the server.
        $server = Server::builder()
            ->setServerInfo('Moodle devtools plugin MCP server', '0.0.1')
            ->addTool([\local_devtools\local\mcp\tools\plugins::class, 'list_plugins'])
            ->addTool([\local_devtools\local\mcp\tools\database::class, 'list_plugin_tables'])
            ->addTool([\local_devtools\local\mcp\tools\lint::class, 'lint_files'])
            ->build();

        $transport = new StdioTransport();
        $server->run($transport);

        return 0;
    }
}
