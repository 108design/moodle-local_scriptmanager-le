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
 * Check that this package can update the installed plugin.
 *
 * @param int $oldversion Previously installed numeric plugin build.
 * @return bool
 */
function xmldb_local_scriptmanager_upgrade(int $oldversion): bool {
    $supportedbuild = $oldversion >= 2026010105 && $oldversion <= 2026093099;
    if (!$supportedbuild) {
        throw new moodle_exception('unsupportedupgrade', 'local_scriptmanager');
    }
    return true;
}
