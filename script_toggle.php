<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Native script toggle entry point for Script Manager.
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

use local_scriptmanager\local\service\factory;

$id = required_param('id', PARAM_INT);
$enabled = required_param('enabled', PARAM_BOOL);
require_login();
if (!data_submitted()) {
    throw new invalid_parameter_exception('POST required.');
}
require_sesskey();
require_capability('local/scriptmanager:manage', context_system::instance());
factory::script_repository()->set_enabled($id, (bool) $enabled, (int) $USER->id);
redirect(
    new moodle_url('/local/scriptmanager/scripts.php'),
    get_string($enabled ? 'scriptactivated' : 'scriptdeactivated', 'local_scriptmanager'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
