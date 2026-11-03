<?php
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
defined('MOODLE_INTERNAL') || die();
function xmldb_local_scriptmanager_upgrade(int $oldversion): bool {
    // This catches direct invocation, after file replacement; Moodle also rejects lower versions.
    if ($oldversion > 2026093099) {
        throw new moodle_exception('cannotdowngrade', 'error');
    }
    return true;
}
