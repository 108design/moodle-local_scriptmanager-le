<?php
/**
 * Edit a managed script draft and publish it explicitly.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */

// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>.
//
// Use and modification are permitted only under the Software License included
// with this distribution. See LICENSE.md for the full terms.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

use local_scriptmanager\form\script_form;
use local_scriptmanager\local\admin_navigation;
use local_scriptmanager\local\service\factory;
use local_scriptmanager\output\ui;

$id = optional_param('id', 0, PARAM_INT);
$restoredversion = optional_param('restoredversion', 0, PARAM_INT);
require_login();
$context = context_system::instance();
require_capability('local/scriptmanager:manage', $context);
\local_scriptmanager\local\module_authority::assert_owner('scriptmanager');
$url = new moodle_url('/local/scriptmanager/script_edit.php', $id ? ['id' => $id] : []);
admin_externalpage_setup('local_scriptmanager');
$repository = factory::script_repository();
$script = $id ? $repository->get($id) : (object) [
    'id' => 0, 'name' => '', 'identifier' => '', 'description' => '', 'enabled' => 0, 'allowfrontpage' => 0,
    'draftrevisionnote' => '',
    'draftcode' => "// context.root is the current document root.\n"
        . "// context.pageType, courseId and categoryId identify the Moodle context.\n",

];
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string($id ? 'editscript' : 'createscript', 'local_scriptmanager'));
$PAGE->set_heading(get_string('pluginname', 'local_scriptmanager'));
$terminal = get_string($id ? 'editscript' : 'createscript', 'local_scriptmanager');
$PAGE->navbar->add($id ? format_string($script->name) : $terminal);
$form = new script_form($url, ['restoredversion' => $restoredversion]);
$form->set_data($script);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/scriptmanager/scripts.php'));
}
if ($data = $form->get_data()) {
    $identifier = strtolower(trim((string) $data->identifier));
    if ($repository->identifier_exists($identifier, (int) $data->id)) {
        redirect(
            $url,
            get_string('scriptidentifierexists', 'local_scriptmanager'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
    $record = (object) ['id' => (int) $data->id, 'name' => trim($data->name), 'identifier' => $identifier,
        'description' => trim((string) $data->description), 'enabled' => (int) $data->enabled,
        'allowfrontpage' => (int) $data->allowfrontpage, 'draftrevisionnote' => trim((string) $data->draftrevisionnote),
        'draftcode' => (string) $data->draftcode];
    $repository->save($record, !empty($data->savepublish), (int) $USER->id);
    redirect(
        new moodle_url('/local/scriptmanager/scripts.php'),
        get_string(!empty($data->savepublish) ? 'scriptpublishedsaved' : 'scriptdraftsaved', 'local_scriptmanager'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}
$PAGE->requires->js_call_amd('local_scriptmanager/source_editor', 'init', [[
    'textareaId' => 'id_draftcode', 'language' => 'javascript',
]]);
\local_scriptmanager\output\context_help::prepare('scripts');
echo $OUTPUT->header();
echo admin_navigation::render('scripts', true);
echo html_writer::start_div('local-scriptmanager-content-narrow');
echo ui::page_header($terminal);
if ($restoredversion) {
    echo $OUTPUT->notification(get_string('scriptrevisionloaded', 'local_scriptmanager', $restoredversion), 'info');
}
$form->display();
echo html_writer::end_div();
echo \local_scriptmanager\output\context_help::render('scripts');
echo $OUTPUT->footer();
