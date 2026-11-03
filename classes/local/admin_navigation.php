<?php
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager\local;
final class admin_navigation {
    public static function render(string $active, bool $linkactive = false): string {
        $url = new \moodle_url('/local/scriptmanager/scripts.php');
        $label = get_string('managescripts', 'local_scriptmanager');
        return \html_writer::div(print_tabs([[new \tabobject('scripts', $url,
            \html_writer::tag('i', '', ['class' => 'fa fa-code icon', 'aria-hidden' => 'true']) . ' ' . s($label),
            $label, $linkactive)]], 'scripts', null, null, true));
    }
}
