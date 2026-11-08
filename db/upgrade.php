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
 * Check and upgrade the installed plugin.
 *
 * @param int $oldversion Previously installed numeric plugin build.
 * @return bool
 */
function xmldb_local_scriptmanager_upgrade(int $oldversion): bool {
    global $DB;

    $supportedbuild = $oldversion >= 2026010105 && $oldversion <= 2026093099;
    if (!$supportedbuild) {
        throw new moodle_exception('unsupportedupgrade', 'local_scriptmanager');
    }
    if ($oldversion < 2026010106) {
        $dbman = $DB->get_manager();
        foreach (['local_scriptmanager_script' => 'enabled', 'local_scriptmanager_scriptver' => 'code'] as $name => $previous) {
            $table = new xmldb_table($name);
            $field = new xmldb_field('allowfrontpage', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', $previous);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2026010106, 'local', 'scriptmanager');
    }
    return true;
}
