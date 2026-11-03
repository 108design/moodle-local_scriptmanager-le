<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// Licensed under the Software License in LICENSE.md.
// Generated from tools/shared-ui/table_presentation.php.in; keep plugin copies independent.
namespace local_scriptmanager\output;

use html_writer;

/** Compact native table values; never applied implicitly to pickers or authored content. */
final class table_presentation {
    public static function init(): void {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_scriptmanager/table_presentation', 'init');
    }

    public static function date(int $timestamp): string {
        return userdate($timestamp, get_string('managerdateformat', 'local_scriptmanager'));
    }

    public static function status(string $label, string $icon, string $tone): string {
        self::init();
        $tone = in_array($tone, ['success', 'warning', 'danger', 'info', 'secondary', 'primary'], true)
            ? $tone : 'secondary';
        return html_writer::span(html_writer::tag('i', '', [
            'class' => 'fa fa-' . $icon . ' icon', 'aria-hidden' => 'true',
        ]), 'cms-table-status text-' . $tone, [
            'role' => 'img', 'aria-label' => $label, 'title' => $label,
            'data-toggle' => 'tooltip', 'tabindex' => '0',
        ]);
    }

    public static function enabled(bool $enabled): string {
        return self::status(get_string($enabled ? 'enabled' : 'disabled', 'core_admin'),
            $enabled ? 'eye' : 'eye-slash', $enabled ? 'success' : 'secondary');
    }

    public static function publication(bool $published): string {
        return self::status(get_string($published ? 'scriptpublished' : 'scriptunpublished', 'local_scriptmanager'),
            $published ? 'check-circle' : 'file-o', $published ? 'success' : 'secondary');
    }
}
