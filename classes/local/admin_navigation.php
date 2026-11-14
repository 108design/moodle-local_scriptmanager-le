<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Render capability-filtered administration navigation.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager\local;

/**
 * Render capability-filtered administration navigation.
 */
final class admin_navigation {
    /**
     * Render the administration navigation permitted for the current user.
     *
     * @param string $active Active route or navigation key.
     * @param bool $linkactive Linkactive.
     * @return string
     */
    public static function render(string $active, bool $linkactive = false): string {
        $url = new \moodle_url('/local/scriptmanager/scripts.php');
        $label = get_string('managescripts', 'local_scriptmanager');
        return \html_writer::div(print_tabs([[new \tabobject(
            'scripts',
            $url,
            \html_writer::tag('i', '', ['class' => 'fa fa-code icon', 'aria-hidden' => 'true']) . ' ' . s($label),
            $label,
            $linkactive
        )]], 'scripts', null, null, true));
    }
}
