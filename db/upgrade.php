<?php
/**
 * Upgrade callbacks for Script Manager.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
defined('MOODLE_INTERNAL') || die();
/**
 * Apply supported schema upgrades for Script Manager.
 *
 * @param int $oldversion Previously installed numeric plugin build.
 * @return bool
 */
function xmldb_local_scriptmanager_upgrade(int $oldversion): bool {
    // This catches direct invocation, after file replacement; Moodle also rejects lower versions.
    if ($oldversion > 2026093099) {
        throw new moodle_exception('cannotdowngrade', 'error');
    }
    return true;
}
