<?php
/**
 * Regression tests for privacy provider.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */

namespace local_scriptmanager;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_scriptmanager\privacy\provider;

/**
 * Functional coverage for Script Manager data export, discovery and attribution erasure.
 */
final class privacy_provider_test extends \advanced_testcase {
    /**
     * Verify that shared script is exported then preserved with anonymised attribution.
     *
     * @return void
     */
    public function test_shared_script_is_exported_then_preserved_with_anonymised_attribution(): void {
        global $DB;
        $this->resetAfterTest();
        $creator = $this->getDataGenerator()->create_user();
        $editor = $this->getDataGenerator()->create_user();
        $context = \context_system::instance();
        $now = time();
        $id = $DB->insert_record('local_scriptmgr_script', (object) [
            'name' => 'Privacy script', 'identifier' => 'privacy-script', 'enabled' => 0,
            'globalenabled' => 0, 'dashboardenabled' => 0, 'sitehomeenabled' => 0,
            'draftcode' => 'window.__privacyTest = true;', 'draftversion' => 1,
            'createdby' => $creator->id, 'modifiedby' => $editor->id,
            'timecreated' => $now, 'timemodified' => $now,
        ]);
        $this->assertEquals([(int) $context->id], array_map(
            'intval',
            provider::get_contexts_for_userid($creator->id)->get_contextids()
        ));
        $users = new userlist($context, 'local_scriptmanager');
        provider::get_users_in_context($users);
        $this->assertEqualsCanonicalizing([$creator->id, $editor->id], $users->get_userids());

        writer::reset();
        provider::export_user_data(new approved_contextlist($creator, 'local_scriptmanager', [$context->id]));
        $data = writer::with_context($context)->get_data([
            get_string('pluginname', 'local_scriptmanager'), 'local_scriptmgr_script', (string) $id,
        ]);
        $this->assertSame('Privacy script', $data->name);
        $this->assertSame('createdby', $data->user_relations);
        $this->assertObjectNotHasProperty('modifiedby', $data);
        provider::delete_data_for_user(
            new approved_contextlist($creator, 'local_scriptmanager', [$context->id])
        );
        $record = $DB->get_record('local_scriptmgr_script', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame(0, (int) $record->createdby);
        $this->assertSame((int) $editor->id, (int) $record->modifiedby);
        $this->assertSame('window.__privacyTest = true;', $record->draftcode);
    }
}
