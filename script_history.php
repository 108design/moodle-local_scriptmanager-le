<?php
/**
 * File documentation for script_history.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
use local_scriptmanager\local\admin_navigation;
use local_scriptmanager\local\service\factory;
use local_scriptmanager\output\ui;
$id = required_param('id', PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('local/scriptmanager:manage', $context);
$url = new moodle_url('/local/scriptmanager/script_history.php', ['id' => $id]);
admin_externalpage_setup('local_scriptmanager');
$repository = factory::script_repository();
$script = $repository->get($id);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('scriptrevisionhistory', 'local_scriptmanager'));
$PAGE->set_heading(get_string('pluginname', 'local_scriptmanager'));
$PAGE->navbar->add(format_string($script->name), new moodle_url('/local/scriptmanager/script_edit.php', ['id' => $id]));
$PAGE->navbar->add(get_string('revisions', 'local_scriptmanager'));
\local_scriptmanager\output\context_help::prepare('scripts');
echo $OUTPUT->header();
echo admin_navigation::render('scripts');
echo ui::page_header(get_string('scriptrevisionhistoryfor', 'local_scriptmanager', format_string($script->name)));
echo $OUTPUT->notification(get_string('scriptrevisionhistorynotice', 'local_scriptmanager'), 'info');
echo html_writer::tag('p', get_string('revisions', 'local_scriptmanager') . ' ' . \local_scriptmanager\output\context_help::icon('scriptrevisions', 'scripts'));
$versions = $repository->versions($id);
$comparefrom = optional_param('from', 0, PARAM_INT);
$compareto = optional_param('to', 0, PARAM_INT);

$orderedversions = array_values($versions);
$previousversions = [];
$versionbyid = [];
foreach ($orderedversions as $v) { $versionbyid[(int)$v->id] = $v; }
foreach ($orderedversions as $i => $v) {
    if (isset($orderedversions[$i + 1])) { $previousversions[$v->id] = $orderedversions[$i + 1]->id; }
}
$table = new html_table();
$table->attributes['class'] = 'generaltable table table-hover w-100 local-scriptmanager-action-table';
$table->head = [get_string('revision', 'local_scriptmanager'), get_string('scriptrevisionnote', 'local_scriptmanager'),
    get_string('scriptdiffsummary', 'local_scriptmanager'), get_string('date'),
    get_string('revisionauthor', 'local_scriptmanager'), get_string('actions')];
foreach ($versions as $version) {
    $user = core_user::get_user($version->publishedby);
    $previous = $versionbyid[$previousversions[$version->id] ?? 0] ?? null;
    $counts = \local_scriptmanager\local\script_revision_diff::characters((string)($previous->code ?? ''), (string)$version->code);
    $content = $counts === null
        ? html_writer::div(get_string('scriptdiffcountunavailable', 'local_scriptmanager'))
        : html_writer::div(get_string('scriptdiffaddedcharacters', 'local_scriptmanager', $counts['add']))
            . html_writer::div(get_string('scriptdiffremovedcharacters', 'local_scriptmanager', $counts['remove']));
    if (!$previous) {
        $content = html_writer::div($content, '', ['title' => get_string('scriptdifffirstrevision', 'local_scriptmanager')]);
    }
    $content = html_writer::div($content, 'local-scriptmanager-script-change-summary');
    $label = get_string('loadrevisionineditor', 'local_scriptmanager');
    $action = ui::icon_post_action(new moodle_url('/local/scriptmanager/script_restore.php',
        ['id' => $id, 'versionid' => $version->id]), $label, 'i/restore');
    {
        $difflabel = get_string(isset($previousversions[$version->id]) ? 'scriptdiffaction' : 'scriptdiffviewrevision', 'local_scriptmanager');
        $action .= html_writer::link(new moodle_url('/local/scriptmanager/script_history.php', ['id' => $id,
            'from' => $previousversions[$version->id] ?? $version->id, 'to' => $version->id], 'script-comparison'),
            html_writer::tag('i', '', ['class' => 'icon fa fa-code-compare fa-fw', 'aria-hidden' => 'true'])
                . html_writer::span($difflabel, 'sr-only'), ['class' => 'local-scriptmanager-icon-button',
                    'title' => $difflabel, 'aria-label' => $difflabel, 'data-toggle' => 'tooltip']);
    }
    $note = trim((string)($version->revisionnote ?? ''));
    $table->data[] = [$version->versionnumber, $note === '' ? '—' : format_text($note, FORMAT_PLAIN),
        $content, \local_scriptmanager\output\table_presentation::date($version->timepublished),
        $user ? fullname($user) : get_string('unknownuser'), ui::table_actions([$action])];
}
echo html_writer::div(html_writer::table($table), 'table-responsive');
if ($comparefrom || $compareto) {
    echo \local_scriptmanager\output\script_revision_comparison::render($versions, $id, $PAGE->url, $comparefrom, $compareto);
}
echo \local_scriptmanager\output\context_help::render('scripts');
echo $OUTPUT->footer();
