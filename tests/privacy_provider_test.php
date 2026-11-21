<?php
// This file is part of Moodle - https://moodle.org/
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
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_scriptmanager\privacy\provider;

/**
 * Functional coverage for Script Manager data export, discovery and attribution erasure.
 */
final class privacy_provider_test extends \advanced_testcase {
    /** Reserved library tables must remain covered even when the script tables are empty. */
    public function test_library_only_attribution_export_and_all_erasure_paths(): void {
        global $DB;
        $this->resetAfterTest();
        $creator = $this->getDataGenerator()->create_user();
        $editor = $this->getDataGenerator()->create_user();
        $context = \context_system::instance();
        $libraryid = $DB->insert_record('local_scriptmanager_library', (object)[
            'name' => 'Shared library', 'identifier' => 'privacy-library-only',
            'draftcode' => '/* shared draft */', 'createdby' => $creator->id, 'modifiedby' => $editor->id,
            'timecreated' => time(), 'timemodified' => time(),
        ]);
        $versionid = $DB->insert_record('local_scriptmanager_libver', (object)[
            'libraryid' => $libraryid, 'versionnumber' => 1, 'code' => '/* shared published version */',
            'contenthash' => hash('sha256', 'shared published version'),
            'publishedby' => $creator->id, 'timepublished' => time(),
        ]);
        $this->assertContains((int)$context->id, array_map('intval',
            provider::get_contexts_for_userid($creator->id)->get_contextids()));
        $DB->set_field('local_scriptmanager_library', 'createdby', 0, ['id' => $libraryid]);
        $this->assertContains((int)$context->id, array_map('intval',
            provider::get_contexts_for_userid($creator->id)->get_contextids()));
        $DB->set_field('local_scriptmanager_library', 'createdby', $creator->id, ['id' => $libraryid]);
        $users = new userlist($context, 'local_scriptmanager');
        provider::get_users_in_context($users);
        $this->assertEqualsCanonicalizing([$creator->id, $editor->id], $users->get_userids());
        $approved = new approved_contextlist($creator, 'local_scriptmanager', [$context->id]);
        writer::reset();
        provider::export_user_data($approved);
        $library = writer::with_context($context)->get_data([
            get_string('pluginname', 'local_scriptmanager'), 'local_scriptmanager_library', (string)$libraryid,
        ]);
        $version = writer::with_context($context)->get_data([
            get_string('pluginname', 'local_scriptmanager'), 'local_scriptmanager_libver', (string)$versionid,
        ]);
        $this->assertSame('createdby', $library->user_relations);
        $this->assertObjectNotHasProperty('modifiedby', $library);
        $this->assertSame('publishedby', $version->user_relations);
        $this->assertObjectNotHasProperty('publishedby', $version);
        provider::delete_data_for_user($approved);
        $this->assertEquals(0, $DB->get_field('local_scriptmanager_library', 'createdby', ['id' => $libraryid]));
        $this->assertEquals($editor->id, $DB->get_field('local_scriptmanager_library', 'modifiedby', ['id' => $libraryid]));
        $this->assertEquals(0, $DB->get_field('local_scriptmanager_libver', 'publishedby', ['id' => $versionid]));
        provider::delete_data_for_users(new approved_userlist($context, 'local_scriptmanager', [$editor->id]));
        $this->assertEquals(0, $DB->get_field('local_scriptmanager_library', 'modifiedby', ['id' => $libraryid]));
        $DB->set_field('local_scriptmanager_libver', 'publishedby', $creator->id, ['id' => $versionid]);
        provider::delete_data_for_all_users_in_context($context);
        $this->assertEquals(0, $DB->get_field('local_scriptmanager_libver', 'publishedby', ['id' => $versionid]));
        $this->assertSame('/* shared published version */', $DB->get_field('local_scriptmanager_libver', 'code', ['id' => $versionid]));
    }

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
        $id = $DB->insert_record('local_scriptmanager_script', (object) [
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
            get_string('pluginname', 'local_scriptmanager'), 'local_scriptmanager_script', (string) $id,
        ]);
        $this->assertSame('Privacy script', $data->name);
        $this->assertSame('createdby', $data->user_relations);
        $this->assertObjectNotHasProperty('modifiedby', $data);
        provider::delete_data_for_user(
            new approved_contextlist($creator, 'local_scriptmanager', [$context->id])
        );
        $record = $DB->get_record('local_scriptmanager_script', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame(0, (int) $record->createdby);
        $this->assertSame((int) $editor->id, (int) $record->modifiedby);
        $this->assertSame('window.__privacyTest = true;', $record->draftcode);
    }
}
