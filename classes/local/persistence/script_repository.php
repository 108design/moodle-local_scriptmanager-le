<?php
/**
 * Persistence boundary for trusted managed scripts.
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
namespace local_scriptmanager\local\persistence;

use moodle_database;
use stdClass;

/**
 * Persistence boundary for trusted managed scripts.
 */
final class script_repository {
    /**
     * Store the dependencies and context used by this helper.
     *
     * @param moodle_database $db Moodle database connection.
     */
    public function __construct(/** @var moodle_database Database connection. */ private readonly moodle_database $db) {
    }

    /**
     * Return all saved records with their relevant state.
     *
     * @return array
     */
    public function get_all(): array {
        return $this->db->get_records('local_scriptmanager_script', null, 'name ASC');
    }

    /**
     * Load the requested managed script.
     *
     * @param int $id Record ID.
     * @return stdClass
     */
    public function get(int $id): stdClass {
        return $this->db->get_record('local_scriptmanager_script', ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * Check whether the stable identifier belongs to another script.
     *
     * @param string $identifier Stable identifier.
     * @param int $ignoreid Ignoreid.
     * @return bool
     */
    public function identifier_exists(string $identifier, int $ignoreid = 0): bool {
        $sql = 'identifier = :identifier' . ($ignoreid ? ' AND id <> :id' : '');
        return $this->db->record_exists_select(
            'local_scriptmanager_script',
            $sql,
            ['identifier' => $identifier] + ($ignoreid ? ['id' => $ignoreid] : [])
        );
    }

    /**
     * Validate and save the managed script draft.
     *
     * @param stdClass $record Record to process.
     * @param bool $publish Whether to publish the saved definition.
     * @param int $userid User ID.
     * @return stdClass
     */
    public function save(stdClass $record, bool $publish, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write(
            'scriptmanager',
            fn() => $this->save_owned($record, $publish, $userid)
        );
    }

    /**
     * Save the script draft after the ownership boundary has been established.
     *
     * @param stdClass $record Record to process.
     * @param bool $publish Whether to publish the saved definition.
     * @param int $userid User ID.
     * @return stdClass
     */
    private function save_owned(stdClass $record, bool $publish, int $userid): stdClass {
        // Only the Frontpage opt-in is editable. Other scope and dependency values are fixed.
        $record = (object) array_intersect_key((array) $record, array_flip([
            'id', 'name', 'identifier', 'description', 'enabled', 'allowfrontpage', 'draftcode', 'draftrevisionnote',
        ]));
        $record = (object) ((array) $record + ['description' => '', 'enabled' => 0, 'draftrevisionnote' => '']);
        $record->allowfrontpage = (int) !empty($record->allowfrontpage);
        if (strlen((string) $record->draftcode) > 500000) {
            throw new \invalid_parameter_exception('Script too large.');
        }
        foreach (self::profile() as $field => $value) {
            $record->$field = $value;
        }
        $transaction = $this->db->start_delegated_transaction();
        $now = time();
        if (empty($record->id)) {
            unset($record->id);
            $record->createdby = $userid;
            $record->timecreated = $now;
            $record->modifiedby = $userid;
            $record->timemodified = $now;
            $record->draftversion = 1;
            $record->id = $this->db->insert_record('local_scriptmanager_script', $record);
        } else {
            $current = $this->get((int) $record->id);
            $record->draftversion = (int) $current->draftversion + 1;
            $record->modifiedby = $userid;
            $record->timemodified = $now;
            $this->db->update_record('local_scriptmanager_script', $record);
        }
        if ($publish) {
            $this->publish_in_transaction((int) $record->id, (string) $record->draftcode, $userid, $now);
        }
        $transaction->allow_commit();
        return $this->get((int) $record->id);
    }

    /**
     * Create an immutable script publication from the current draft.
     *
     * @param int $id Record ID.
     * @param int $userid User ID.
     * @return stdClass
     */
    public function publish(int $id, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write(
            'scriptmanager',
            fn() => $this->publish_owned($id, $userid)
        );
    }

    /**
     * Publish the script draft after the ownership boundary has been established.
     *
     * @param int $id Record ID.
     * @param int $userid User ID.
     * @return stdClass
     */
    private function publish_owned(int $id, int $userid): stdClass {
        $transaction = $this->db->start_delegated_transaction();
        $script = $this->get($id);
        $now = time();
        $this->publish_in_transaction($id, (string) $script->draftcode, $userid, $now);
        $this->db->update_record('local_scriptmanager_script', (object) [
            'id' => $id,
            'modifiedby' => $userid,
            'timemodified' => $now,
        ]);
        $transaction->allow_commit();
        return $this->get($id);
    }

    /**
     * Set future runtime delivery of the published script.
     *
     * @param int $id Record ID.
     * @param bool $enabled Whether future delivery is enabled.
     * @param int $userid User ID.
     * @return stdClass
     */
    public function set_enabled(int $id, bool $enabled, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write(
            'scriptmanager',
            fn() => $this->set_enabled_owned($id, $enabled, $userid)
        );
    }

    /**
     * Set script enablement after checking module ownership.
     *
     * @param int $id Record ID.
     * @param bool $enabled Whether future delivery is enabled.
     * @param int $userid User ID.
     * @return stdClass
     */
    private function set_enabled_owned(int $id, bool $enabled, int $userid): stdClass {
        $this->get($id);
        $this->db->update_record('local_scriptmanager_script', (object) [
            'id' => $id,
            'enabled' => (int) $enabled,
            'modifiedby' => $userid,
            'timemodified' => time(),
        ]);
        return $this->get($id);
    }
    /**
     * Return the immutable published versions of the script.
     *
     * @param int $id Record ID.
     * @return array
     */
    public function versions(int $id): array {
        return $this->db->get_records('local_scriptmanager_scriptver', ['scriptid' => $id], 'versionnumber DESC');
    }
    /**
     * Restore the published script version into a draft.
     *
     * @param int $id Record ID.
     * @param int $versionid Published version ID.
     * @param int $userid User ID.
     * @return stdClass
     */
    public function restore(int $id, int $versionid, int $userid): stdClass {
        return \local_scriptmanager\local\module_authority::write(
            'scriptmanager',
            fn() => $this->restore_owned($id, $versionid, $userid)
        );
    }

    /**
     * Restore a published version into a draft under the ownership boundary.
     *
     * @param int $id Record ID.
     * @param int $versionid Published version ID.
     * @param int $userid User ID.
     * @return stdClass
     */
    private function restore_owned(int $id, int $versionid, int $userid): stdClass {
        $version = $this->db->get_record(
            'local_scriptmanager_scriptver',
            ['id' => $versionid, 'scriptid' => $id],
            '*',
            MUST_EXIST
        );
        $script = $this->get($id);
        $script->draftcode = $version->code;
        $script->allowfrontpage = $version->allowfrontpage;
        $script->draftrevisionnote = '';
        $this->save($script, false, $userid);
        return $version;
    }
    /**
     * Delete the managed script and its stored publications.
     *
     * @param int $id Record ID.
     * @return void
     */
    public function delete(int $id): void {
        \local_scriptmanager\local\module_authority::write('scriptmanager', fn() => $this->delete_owned($id));
    }

    /**
     * Delete the script and its versions under the ownership boundary.
     *
     * @param int $id Record ID.
     * @return void
     */
    private function delete_owned(int $id): void {
        $this->get($id);
        $this->db->delete_records('local_scriptmanager_scriptver', ['scriptid' => $id]);
        $this->db->delete_records('local_scriptmanager_script', ['id' => $id]);
    }

    /**
     * Return enabled published scripts eligible for runtime selection.
     *
     * @param bool $frontpageonly Require the published Frontpage opt-in.
     * @return array
     */
    public function get_context_candidates(bool $frontpageonly = false): array {
        $frontpagefilter = $frontpageonly ? ' AND v.allowfrontpage = 1' : '';
        return $this->db->get_records_sql('SELECT s.id, s.name, s.identifier, s.enabled,
            v.versionnumber, v.id AS versionid FROM {local_scriptmanager_script} s
            JOIN {local_scriptmanager_scriptver} v ON v.id = s.publishedversionid
            WHERE s.enabled = 1' . $frontpagefilter . ' ORDER BY s.name ASC');
    }

    /**
     * Reserved schema values preserve the meaning of existing scripts after a later upgrade.
     *
     * @return array
     */
    private static function profile(): array {
        return ['globalenabled' => 1, 'dashboardenabled' => 0, 'sitehomeenabled' => 0,
            'courseallowlist' => '', 'coursedenylist' => '', 'categoryallowlist' => '', 'categorydenylist' => '',
            'dependenciesjson' => '[]'];
    }

    /**
     * Create an immutable publication inside the current transaction.
     *
     * @param int $id Record ID.
     * @param string $code Code.
     * @param int $userid User ID.
     * @param int $now Now.
     * @return void
     */
    private function publish_in_transaction(int $id, string $code, int $userid, int $now): void {
        $script = $this->get($id);
        $number = 1 + (int) $this->db->get_field_sql(
            'SELECT COALESCE(MAX(versionnumber), 0) FROM {local_scriptmanager_scriptver} WHERE scriptid = ?',
            [$id]
        );
        $versionid = $this->db->insert_record('local_scriptmanager_scriptver', (object) [
            'scriptid' => $id,
            'versionnumber' => $number,
            'code' => $code,
            'allowfrontpage' => (int) $script->allowfrontpage,
            ...self::profile(),
            'revisionnote' => $script->draftrevisionnote,
            'publishedby' => $userid,
            'timepublished' => $now,
        ]);
        $this->db->update_record('local_scriptmanager_script', (object) [
            'id' => $id,
            'publishedversionid' => $versionid,
            'draftrevisionnote' => '',
        ]);
    }
}
