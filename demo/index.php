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
 * Empty page to demonstrate the debugbar in action.
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\output\html_writer;
use core\url;
use Symfony\Component\VarDumper\VarDumper;

require_once(__DIR__ . '/../../../config.php');

require_login();

$url = new url('/local/devtools/demo/index.php');
$context = system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);

echo $OUTPUT->header();

echo html_writer::tag(
    'div',
    join(array_map(fn($line) => html_writer::tag('code', $line . '<br>'), [
        '$transaction = $DB->start_delegated_transaction();',
        '$data = $DB->get_records("user", ["id" => $USER->id]);',
        '$transaction->allow_commit();',
    ]))
);

$transaction = $DB->start_delegated_transaction();
$data = $DB->get_records('user', ['id' => $USER->id]);
$transaction->allow_commit();

VarDumper::dump($data);


echo html_writer::tag(
    'div',
    join(array_map(fn($line) => html_writer::tag('code', $line . '<br>'), [
        '$transaction = $DB->start_delegated_transaction();',
        '$data = $DB->get_records("user", ["id" => $USER->id]);',
        '$transaction->rollback(new \Exception("Rolling back transaction for demonstration purposes."));',
    ]))
);

$transaction = $DB->start_delegated_transaction();
$data = $DB->get_records('user', ['id' => $USER->id]);
$transaction->rollback(new \Exception('Rolling back transaction for demonstration purposes.'));

VarDumper::dump($data);

echo $OUTPUT->footer();
