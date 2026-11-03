<?php
/**
 * File documentation for classes/local/persistence/script_repository.php.
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
namespace local_scriptmanager\local\persistence;

use moodle_database;
use stdClass;

/** Persistence boundary for trusted managed scripts. */
final class script_repository {
    public function __construct(private readonly moodle_database $db) {
    }

    public function get_all(): array {
        return $this->db->get_records('local_scriptmgr_script', null, 'name ASC');
    }

    public function get(int $id): stdClass {
        return $this->db->get_record('local_scriptmgr_script', ['id' => $id], '*', MUST_EXIST);
    }

    public function identifier_exists(string $identifier, int $ignoreid = 0): bool {
        $sql = 'identifier = :identifier' . ($ignoreid ? ' AND id <> :id' : '');
        return $this->db->record_exists_select('local_scriptmgr_script', $sql,
            ['identifier' => $identifier] + ($ignoreid ? ['id' => $ignoreid] : []));
    }

    public function save(stdClass $record, bool $publish, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->save_owned($record, $publish, $userid));
    }

    private function save_owned(stdClass $record, bool $publish, int $userid): stdClass {
        // Accept only script fields. Scope and dependency values are fixed compatibility data.
        $record = (object) array_intersect_key((array) $record, array_flip([
            'id', 'name', 'identifier', 'description', 'enabled', 'draftcode', 'draftrevisionnote',
        ]));
        $record = (object) ((array) $record + ['description' => '', 'enabled' => 0, 'draftrevisionnote' => '']);
        if (strlen((string) $record->draftcode) > 500000) { throw new \invalid_parameter_exception('Script too large.'); }
        foreach (self::profile() as $field => $value) { $record->$field = $value; }
        $transaction = $this->db->start_delegated_transaction();
        $now = time();
        if (empty($record->id)) {
            unset($record->id);
            $record->createdby = $userid;
            $record->timecreated = $now;
            $record->modifiedby = $userid;
            $record->timemodified = $now;
            $record->draftversion = 1;
            $record->id = $this->db->insert_record('local_scriptmgr_script', $record);
        } else {
            $current = $this->get((int) $record->id);
            $record->draftversion = (int) $current->draftversion + 1;
            $record->modifiedby = $userid;
            $record->timemodified = $now;
            $this->db->update_record('local_scriptmgr_script', $record);
        }
        if ($publish) {
            $this->publish_in_transaction((int) $record->id, (string) $record->draftcode, $userid, $now);
        }
        $transaction->allow_commit();
        return $this->get((int) $record->id);
    }

    public function publish(int $id, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->publish_owned($id, $userid));
    }

    private function publish_owned(int $id, int $userid): stdClass {
        $transaction = $this->db->start_delegated_transaction();
        $script = $this->get($id);
        $now = time();
        $this->publish_in_transaction($id, (string) $script->draftcode, $userid, $now);
        $this->db->update_record('local_scriptmgr_script', (object) [
            'id' => $id,
            'modifiedby' => $userid,
            'timemodified' => $now,
        ]);
        $transaction->allow_commit();
        return $this->get($id);
    }

    public function set_enabled(int $id, bool $enabled, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->set_enabled_owned($id, $enabled, $userid));
    }

    private function set_enabled_owned(int $id, bool $enabled, int $userid): stdClass {
        $this->get($id);
        $this->db->update_record('local_scriptmgr_script', (object) [
            'id' => $id,
            'enabled' => (int) $enabled,
            'modifiedby' => $userid,
            'timemodified' => time(),
        ]);
        return $this->get($id);
    }
    public function versions(int $id): array { return $this->db->get_records('local_scriptmgr_scriptver',['scriptid'=>$id],'versionnumber DESC'); }
    public function restore(int $id,int $versionid,int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->restore_owned($id, $versionid, $userid));
    }

    private function restore_owned(int $id, int $versionid, int $userid): stdClass {
        $version = $this->db->get_record('local_scriptmgr_scriptver',
            ['id' => $versionid, 'scriptid' => $id], '*', MUST_EXIST);
        $script = $this->get($id);
        $script->draftcode = $version->code;
        $script->draftrevisionnote = '';
        $this->save($script, false, $userid);
        return $version;
    }
    public function delete(int $id): void {
        \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->delete_owned($id));
    }

    private function delete_owned(int $id): void { $this->get($id); $this->db->delete_records('local_scriptmgr_scriptver',['scriptid'=>$id]); $this->db->delete_records('local_scriptmgr_script',['id'=>$id]); }

    public function get_context_candidates(): array {
        return $this->db->get_records_sql('SELECT s.id, s.name, s.identifier, s.enabled,
            v.versionnumber, v.id AS versionid FROM {local_scriptmgr_script} s
            JOIN {local_scriptmgr_scriptver} v ON v.id = s.publishedversionid
            WHERE s.enabled = 1 ORDER BY s.name ASC');
    }

    /** Reserved schema values preserve the meaning of existing scripts after a later upgrade. */
    private static function profile(): array {
        return ['globalenabled' => 1, 'dashboardenabled' => 0, 'sitehomeenabled' => 0,
            'courseallowlist' => '', 'coursedenylist' => '', 'categoryallowlist' => '', 'categorydenylist' => '',
            'dependenciesjson' => '[]'];
    }

    private function publish_in_transaction(int $id, string $code, int $userid, int $now): void {
        $script = $this->get($id);
        $number = 1 + (int) $this->db->get_field_sql(
            'SELECT COALESCE(MAX(versionnumber), 0) FROM {local_scriptmgr_scriptver} WHERE scriptid = ?', [$id]);
        $versionid = $this->db->insert_record('local_scriptmgr_scriptver', (object) [
            'scriptid' => $id,
            'versionnumber' => $number,
            'code' => $code,
            ...self::profile(),
            'revisionnote' => $script->draftrevisionnote,
            'publishedby' => $userid,
            'timepublished' => $now,
        ]);
        $this->db->update_record('local_scriptmgr_script', (object) [
            'id' => $id,
            'publishedversionid' => $versionid,
            'draftrevisionnote' => '',
        ]);
    }
}
