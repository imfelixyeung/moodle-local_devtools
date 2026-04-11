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

/**
 * CLI script to execute the xmldb tool.
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/ddllib.php');

[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'action' => null,
    ],
    [
        'h' => 'help',
        'a' => 'action',
    ]
);

if ($options['help']) {
    echo <<<HELP
    Runs the xmldb admin tool directly via the CLI.

    Options:
     -h, --help               Print out this help
     -a, --action             Execute an action
     --[key]=[value]          Pass key value pairs to the action

    Examples:
    - xmldb.php --action=create_xml_file --dir=/local/plugin/db
    - xmldb.php --action=view_xml --file=/local/devtools/db/install.xml
    HELP . PHP_EOL;
    die;
}

// Lets fake being a HTTP request.
$_POST['sesskey'] = sesskey();
if ($unrecognized) {
    foreach ($unrecognized as $value) {
        if (!str_starts_with($value, "--")) {
            cli_error("Unknown args $value");
            die;
        }
        $value = substr($value, strlen("--"));
        $temparray = explode("=", $value);
        $key = array_shift($temparray);
        $value = implode("=", $temparray);

        $_POST[$key] = $value;
    }
}

$actionsdir = "$CFG->dirroot/$CFG->admin/tool/xmldb/actions";
$actions = scandir($actionsdir);

/** @var class-string<XMLDBAction> $selectedaction */
$selectedaction = $options['action'];

if (!$selectedaction) {
    cli_writeln("You must specify --action, available actions are:");
    foreach ($actions as $action) {
        cli_writeln("- $action");
    }
    die;
}

if (!in_array($selectedaction, $actions)) {
    cli_error("Invalid action");
    die;
}

require_once("$actionsdir/XMLDBAction.class.php");
require_once("$actionsdir/XMLDBCheckAction.class.php");

$action = new XMLDBAction();
$okay = $action->launch($selectedaction);

if (!$okay) {
    cli_error($action->getError());
    die;
}

echo $action->getOutput();
