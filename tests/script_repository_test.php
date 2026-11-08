<?php
/**
 * Regression tests for script repository.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager;

use local_scriptmanager\local\service\factory;
use local_scriptmanager\local\module_authority;
use local_scriptmanager\local\context_script_resolver;

/**
 * Native persistence, fixed-profile and recovery checks with synthetic code only.
 */
final class script_repository_test extends \advanced_testcase {
    /**
     * Prepare the isolated native fixtures for the test.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('moduleowner', 'local_scriptmanager', 'local_scriptmanager');
    }
    /**
     * Verify that draft publication restore and fixed profile.
     *
     * @return void
     */
    public function test_draft_publication_restore_and_fixed_profile(): void {
        global $USER, $DB;
        $repo = factory::script_repository();
        $a = $repo->save(
            (object)[
            'name' => 'Alpha',
            'identifier' => 'alpha',
            'draftcode' => 'context.root.dataset.alpha="one";',

            'globalenabled' => 0,
            'dashboardenabled' => 1,
            'courseallowlist' => '42',
            'dependenciesjson' => '[999]',
            ],
            false,
            $USER->id
        );
        $this->assertEmpty($a->enabled);
        $this->assertEmpty($a->publishedversionid);
        $this->assertEquals(1, $a->globalenabled);
        $this->assertEquals(0, $a->dashboardenabled);
        $this->assertSame('', $a->courseallowlist);
        $this->assertSame('[]', $a->dependenciesjson);
        $a = $repo->publish($a->id, $USER->id);
        $first = $a->publishedversionid;
        $a->draftcode = 'context.root.dataset.alpha="two";';
        $a = $repo->save($a, false, $USER->id);
        $this->assertEquals($first, $a->publishedversionid);
        $this->assertCount(1, $repo->versions($a->id));
        $a = $repo->publish($a->id, $USER->id);
        $second = $a->publishedversionid;
        // Legacy or injected version metadata cannot expand the fixed execution profile.
        $DB->update_record('local_scriptmanager_scriptver', (object)['id' => $first, 'globalenabled' => 0,
            'dashboardenabled' => 1, 'courseallowlist' => '42', 'dependenciesjson' => '[999]']);
        $repo->restore($a->id, $first, $USER->id);
        $a = $repo->get($a->id);
        $this->assertEquals($second, $a->publishedversionid);
        $this->assertStringContainsString('one', $a->draftcode);
        $this->assertSame('[]', $a->dependenciesjson);
        $this->assertSame('', $a->courseallowlist);
        $this->assertEquals(1, $a->globalenabled);
        $this->assertEquals(0, $a->dashboardenabled);
        $this->assertCount(2, $repo->versions($a->id));
        $repo->publish($a->id, $USER->id);
        $this->assertCount(3, $repo->versions($a->id));
        $repo->set_enabled($a->id, true, $USER->id);
        $b = $repo->save(
            (object)[
            'name' => 'Beta',
            'identifier' => 'beta',
            'draftcode' => 'context.root.dataset.beta="ok";',
            ],
            true,
            $USER->id
        );
        $repo->set_enabled($b->id, true, $USER->id);
        $this->assertEquals([$a->id, $b->id], array_keys($repo->get_context_candidates()));
        $repo->set_enabled($a->id, false, $USER->id);
        $this->assertCount(1, $repo->get_context_candidates());
        $repo->delete($b->id);
        $this->assertFalse($DB->record_exists('local_scriptmanager_scriptver', ['scriptid' => $b->id]));
    }
    /**
     * Verify that last saved draft wins for parallel editors.
     *
     * @return void
     */
    public function test_last_saved_draft_wins_for_parallel_editors(): void {
        global $USER;
        $repo = factory::script_repository();
        $script = $repo->save(
            (object)[ 'name' => 'Alpha', 'identifier' => 'alpha', 'draftcode' => 'void 0;' ],
            false,
            $USER->id
        );
        $first = $repo->get($script->id);
        $second = $repo->get($script->id);
        $first->draftcode = 'void 1;';
        $repo->save($first, false, $USER->id);
        $second->draftcode = 'void 2;';
        $saved = $repo->save($second, false, $USER->id);
        $this->assertSame('void 2;', $saved->draftcode);
        $this->assertEquals(3, $saved->draftversion);
    }
    /**
     * Verify that versions are scoped to their script.
     *
     * @return void
     */
    public function test_versions_are_scoped_to_their_script(): void {
        global $USER;
        $repo = factory::script_repository();
        $a = $repo->save(
            (object)[ 'name' => 'Alpha', 'identifier' => 'alpha', 'draftcode' => 'void 0;' ],
            true,
            $USER->id
        );
        $b = $repo->save((object)['name' => 'Beta', 'identifier' => 'beta', 'draftcode' => 'void 0;'], true, $USER->id);
        $this->expectException(\dml_missing_record_exception::class);
        $repo->restore($a->id, $b->publishedversionid, $USER->id);
    }
    /**
     * Verify that non administrator cannot write.
     *
     * @return void
     */
    public function test_non_administrator_cannot_write(): void {
        global $USER;
        $repo = factory::script_repository();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        $repo->save(
            (object)[ 'name' => 'Blocked', 'identifier' => 'blocked', 'draftcode' => 'void 0;' ],
            false,
            $USER->id
        );
    }
    /**
     * Verify that existing owner is preserved.
     *
     * @return void
     */
    public function test_existing_owner_is_preserved(): void {
        set_config('moduleowner', 'local_frontpage', 'local_scriptmanager');
        module_authority::initialise('scriptmanager');
        $this->assertSame('local_frontpage', module_authority::owner('scriptmanager'));
        $this->expectException(\moodle_exception::class);
        factory::script_repository();
    }
    /**
     * Verify that permitted pages and recovery exclusions.
     *
     * @return void
     */
    public function test_permitted_pages_and_recovery_exclusions(): void {
        global $USER;
        $repo = factory::script_repository();
        $a = $repo->save(
            (object)[ 'name' => 'Alpha', 'identifier' => 'alpha', 'draftcode' => 'void 0;', 'enabled' => 1 ],
            true,
            $USER->id
        );
        $resolver = new context_script_resolver($repo);
        foreach (['site-index', 'my-index', 'course-view-topics', 'course-index-category', 'mod-page-view'] as $type) {
            $page = new \moodle_page();
            $page->set_context(\context_system::instance());
            $page->set_pagetype($type);
            $page->set_pagelayout('standard');
            $this->assertCount(1, $resolver->resolve($page), $type);
        }
        foreach (
            [
            'admin-index',
            'login-index',
            'auth-test',
            'tool-test',
            'local-scriptmanager-scripts',
            'local-frontpage-index',
            'mod-frontpage-view',
            ] as $type
        ) {
            $page = new \moodle_page();
            $page->set_context(\context_system::instance());
            $page->set_pagetype($type);
            $page->set_pagelayout('standard');
            $this->assertSame([], $resolver->resolve($page), $type);
        }
        foreach (['embedded', 'popup', 'maintenance', 'print', 'redirect'] as $layout) {
            $page = new \moodle_page();
            $page->set_context(\context_system::instance());
            $page->set_pagetype('course-view-topics');
            $page->set_pagelayout($layout);
            $this->assertSame([], $resolver->resolve($page), $layout);
        }
    }

    /**
     * The published version controls Frontpage delivery independently of its draft.
     */
    public function test_frontpage_delivery_requires_a_published_opt_in(): void {
        global $USER;
        $repo = factory::script_repository();
        $script = $repo->save((object) [
            'name' => 'Alpha', 'identifier' => 'alpha', 'draftcode' => 'void 0;', 'enabled' => 1,
        ], true, $USER->id);
        $firstversion = $script->publishedversionid;
        $resolver = new context_script_resolver($repo);
        $page = new \moodle_page();
        $page->set_context(\context_system::instance());
        $page->set_pagetype('local-frontpage-view');
        $page->set_pagelayout('standard');
        $page->set_url(new \moodle_url('/local/frontpage/view.php', ['id' => 1]));
        $this->assertSame([], $resolver->resolve($page));

        $script->allowfrontpage = 1;
        $repo->save($script, false, $USER->id);
        $this->assertSame([], $resolver->resolve($page));
        $repo->publish($script->id, $USER->id);
        $this->assertSame([(int)$script->id], array_keys($resolver->resolve($page)));
        foreach ([['preview' => 1], ['revisionid' => 2]] as $params) {
            $page->set_url(new \moodle_url('/local/frontpage/view.php', ['id' => 1] + $params));
            $this->assertSame([], $resolver->resolve($page));
        }
        $page->set_pagetype('local-frontpage-edit');
        $page->set_url(new \moodle_url('/local/frontpage/edit.php', ['id' => 1]));
        $this->assertSame([], $resolver->resolve($page));
        $page->set_pagetype('mod-frontpage-view');
        $page->set_pagelayout('incourse');
        $page->set_url(new \moodle_url('/mod/frontpage/view.php', ['id' => 1]));
        $this->assertSame([(int)$script->id], array_keys($resolver->resolve($page)));

        $repo->restore($script->id, $firstversion, $USER->id);
        $this->assertEmpty($repo->get($script->id)->allowfrontpage);
        $this->assertSame([(int)$script->id], array_keys($resolver->resolve($page)));
        $repo->publish($script->id, $USER->id);
        $this->assertSame([], $resolver->resolve($page));
    }
}
