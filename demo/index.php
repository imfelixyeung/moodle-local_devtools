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
 *
 * @var moodle_database $DB
 * @var stdClass $USER
 * @var moodle_page $PAGE
 * @var core_renderer $OUTPUT
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\output\html_writer;
use core\url;
use local_devtools\local\debugbar;
use local_devtools\local\debugbar\log_level;
use Symfony\Component\VarDumper\VarDumper;

require_once(__DIR__ . '/../../../config.php');

require_login();

$shouldredirect = optional_param('redirect', false, PARAM_INT);

$url = new url('/local/devtools/demo/index.php');
$context = system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->add_header_action(
    $OUTPUT->single_button(
        new url($url, ['redirect' => 5]),
        "Redirect 5 times"
    )
);

if ($shouldredirect) {
    $DB->get_records('user', ['id' => -$shouldredirect]);
    redirect(new url($url, ['redirect' => $shouldredirect - 1]));
}

echo $OUTPUT->header();

echo html_writer::tag(
    'div',
    join(array_map(fn($line) => html_writer::tag('code', $line . '<br>'), [
        '$transaction = $DB->start_delegated_transaction();',
        '$data = $DB->get_records("user", ["id" => $USER->id]);',
        '$transaction->allow_commit();',
    ]))
);

try {
    $transaction = $DB->start_delegated_transaction();
    $data = $DB->get_records('user', ['id' => $USER->id]);
    $transaction->allow_commit();

    VarDumper::dump($data);
} catch (\Throwable $th) {
    debugbar::instance()->log_exception($th);
    VarDumper::dump($th);
}


echo html_writer::tag(
    'div',
    join(array_map(fn($line) => html_writer::tag('code', $line . '<br>'), [
        '$transaction = $DB->start_delegated_transaction();',
        '$data = $DB->get_records("user", ["id" => $USER->id]);',
        '$transaction->rollback(new \Exception("Rolling back transaction for demonstration purposes."));',
    ]))
);

try {
    $transaction = $DB->start_delegated_transaction();
    $data = $DB->get_records('user', ['id' => $USER->id]);
    $transaction->rollback(new \Exception('Rolling back transaction for demonstration purposes.'));

    VarDumper::dump($data);
} catch (\Throwable $th) {
    debugbar::instance()->log_exception($th);
    VarDumper::dump($th);
}

echo html_writer::tag(
    'div',
    html_writer::tag('code', 'Dumping the $DB global variable to show the wrapped PDO connection.')
);
VarDumper::dump($DB);

// Logging to the messages area.
debugbar::log('Information');
debugbar::log('Oops, an error', log_level::ERROR);
debugbar::log('Warning!!', log_level::WARNING);
debugbar::log((object) [
    'Objects' => 'Are supported too',
]);
debugbar::log(new Exception('Exceptions'), log_level::CRITICAL);

/**
 * Slow function for demo
 * @param string $id
 * @return string
 */
function slow_function(string $id) {
    sleep(1);
    usleep(248160);
    return "Slow function ID=$id completed";
}

$slowfuncresults = debugbar::measure('a slow function', fn() => slow_function('A'));
echo html_writer::div($slowfuncresults);

$duration = null;
$slowfuncresults = debugbar::measure('a slow function', fn() => slow_function('B'), duration: $duration);
echo html_writer::div($slowfuncresults);
echo html_writer::div("That took {$duration}s!");

echo $OUTPUT->footer();
