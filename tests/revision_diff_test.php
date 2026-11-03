<?php
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager;
use local_scriptmanager\local\script_revision_diff;
final class revision_diff_test extends \basic_testcase {
    public function test_comparison_keeps_source_as_data(): void {
        $diff=script_revision_diff::operations("alpha\nbeta", "alpha\ngamma");
        $this->assertContains(['remove','beta'],$diff['operations']);
        $this->assertContains(['add','gamma'],$diff['operations']);
        $this->assertNotEmpty(script_revision_diff::hunks($diff['operations']));
    }
    public function test_foreign_revision_is_rejected(): void {
        $this->expectException(\invalid_parameter_exception::class);
        script_revision_diff::select([(object)['id'=>9,'scriptid'=>2,'versionnumber'=>1]],1,9,9);
    }
}
