<?php
/**
 * File documentation for scripts.php.
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
// with this distribution. See LICENSE.md for the full terms.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_scriptmanager\local\service\factory;
use local_scriptmanager\local\admin_navigation;
use local_scriptmanager\output\ui;

require_login();
$context = context_system::instance();
require_capability('local/scriptmanager:manage', $context);
$url = new moodle_url('/local/scriptmanager/scripts.php');
admin_externalpage_setup('local_scriptmanager', '', null, $url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managescripts', 'local_scriptmanager'));
$PAGE->set_heading(get_string('pluginname', 'local_scriptmanager'));

$PAGE->requires->js_call_amd('local_scriptmanager/search_managers', 'init');
\local_scriptmanager\output\context_help::prepare('scripts');
echo $OUTPUT->header();
echo admin_navigation::render('scripts');
if (!\local_scriptmanager\local\module_authority::is_owner('scriptmanager')) {
    echo \local_scriptmanager\local\module_authority::notice('scriptmanager');
    echo $OUTPUT->footer();
    exit;
}
$pageactions = [html_writer::link(new moodle_url('/local/scriptmanager/script_edit.php'),
    $OUTPUT->pix_icon('t/add', '') . html_writer::span(get_string('createscript', 'local_scriptmanager'),
        'local-scriptmanager-ms-1'), ['class' => 'btn btn-primary'])];
echo ui::page_header(get_string('managescripts', 'local_scriptmanager') . ' ' . \local_scriptmanager\output\context_help::icon('scriptbasics'), $pageactions);
echo $OUTPUT->notification(get_string('scriptmanagernotice', 'local_scriptmanager'), 'warning');
$scripts = factory::read_script_repository()->get_all();
$searchquery = trim(optional_param('scriptq', '', PARAM_TEXT));
$searchstate = optional_param('scriptstate', '', PARAM_ALPHA);
$searchpublication = optional_param('scriptpublication', '', PARAM_ALPHA);
echo html_writer::start_div('', ['data-search-bar-scope' => 'local_scriptmanager-scripts']);
echo \local_scriptmanager\output\search_bar::controls(new moodle_url('/local/scriptmanager/scripts.php'), [
    'scriptq' => ['label' => get_string('search'), 'value' => $searchquery,
        'placeholder' => get_string('searchbarscripts', 'local_scriptmanager')],
    'scriptstate' => ['label' => get_string('status'), 'value' => $searchstate,
        'options' => ['' => get_string('all'), 'enabled' => get_string('enabled', 'core_admin'),
            'disabled' => get_string('disabled', 'core_admin')]],
    'scriptpublication' => ['label' => get_string('searchbarpublication', 'local_scriptmanager'), 'value' => $searchpublication,
        'options' => ['' => get_string('all'), 'published' => get_string('scriptpublished', 'local_scriptmanager'),
            'draft' => get_string('scriptunpublished', 'local_scriptmanager')]],
], 'local_scriptmanager-scripts');
echo html_writer::start_div('', ['data-search-bar-results' => '1']);
$scripts = array_filter($scripts, static fn(object $item): bool =>
    ($searchquery === '' || str_contains(core_text::strtolower($item->name . ' ' . $item->identifier . ' '
        . $item->description), core_text::strtolower($searchquery)))
    && ($searchstate === '' || (bool)$item->enabled === ($searchstate === 'enabled'))
    && ($searchpublication === '' || (bool)$item->publishedversionid === ($searchpublication === 'published')));

$confirmationmodals = [];
if (!$scripts) {
    echo $OUTPUT->notification(get_string('noscripts', 'local_scriptmanager'), 'info');
} else {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable table table-hover w-100 local-scriptmanager-action-table local-scriptmanager-script-library-table';
    $table->head = [get_string('tableheadingname', 'local_scriptmanager'), get_string('description'), html_writer::span(get_string('status') . ' ' . \local_scriptmanager\output\context_help::icon('scriptpublication', 'scripts'), 'text-nowrap'),
        get_string('tableheadingpublished', 'local_scriptmanager'), get_string('tableheadingmodified', 'local_scriptmanager'), get_string('actions')];
    foreach ($scripts as $script) {
        $status = \local_scriptmanager\output\table_presentation::enabled((bool) $script->enabled);
        $publication = \local_scriptmanager\output\table_presentation::publication(!empty($script->publishedversionid));
        $editurl = new moodle_url('/local/scriptmanager/script_edit.php', ['id' => $script->id]);
        $actions = [ui::icon_action($editurl,
            get_string('edit'), 't/edit')];
        $togglelabel = get_string($script->enabled ? 'deactivatescript' : 'activatescript', 'local_scriptmanager');
        {
            $actions[] = ui::icon_post_action(new moodle_url('/local/scriptmanager/script_publish.php', [
                'id' => $script->id, 'sesskey' => sesskey(),
            ]), get_string('publishscript', 'local_scriptmanager'), 'i/publish');
            $actions[] = ui::icon_action(new moodle_url('/local/scriptmanager/script_history.php', ['id'=>$script->id]),
                get_string('revisions', 'local_scriptmanager'), 'i/restore');
            $actions[] = ui::icon_post_action(new moodle_url('/local/scriptmanager/script_toggle.php', [
                'id' => $script->id, 'enabled' => $script->enabled ? 0 : 1, 'sesskey' => sesskey(),
            ]), $togglelabel, $script->enabled ? 't/hide' : 't/show');
            $deletemodalid = 'local-scriptmanager-delete-script-' . $script->id;
            $actions[] = ui::icon_modal_action($deletemodalid, get_string('delete'), 't/delete',
                'local-scriptmanager-action-danger');
            $confirmationmodals[] = ui::confirmation_modal($deletemodalid,
                get_string('deletescript', 'local_scriptmanager'),
                get_string('deletescriptconfirm', 'local_scriptmanager', format_string($script->name)),
                new moodle_url('/local/scriptmanager/script_delete.php', [
                    'id' => $script->id, 'confirm' => 1, 'sesskey' => sesskey(),
                ]), get_string('delete'));
        }
        $description = trim((string) $script->description);
        $description = $description === '' ? '—' : s(shorten_text($description, 140, true));
        $table->data[] = [html_writer::link($editurl, format_string($script->name)) .
            html_writer::div(s($script->identifier), 'text-muted small'), $description, $status, $publication,
            \local_scriptmanager\output\table_presentation::date($script->timemodified), ui::table_actions($actions)];
    }
    echo html_writer::div(html_writer::table($table), 'table-responsive local-scriptmanager-table-responsive');
}
echo implode('', $confirmationmodals);
echo html_writer::end_div() . html_writer::end_div();
echo \local_scriptmanager\output\context_help::render('scripts');
echo $OUTPUT->footer();
