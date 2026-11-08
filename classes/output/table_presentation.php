<?php
/**
 * Compact native table values; never applied implicitly to pickers or authored content.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>.
// Licensed under the Software License in LICENSE.md.
// Generated from tools/shared-ui/table_presentation.php.in; keep plugin copies independent.
namespace local_scriptmanager\output;

use html_writer;

/**
 * Compact native table values; never applied implicitly to pickers or authored content.
 */
final class table_presentation {
    /**
     * Register the assets used by the table presentation.
     *
     * @return void
     */
    public static function init(): void {
        global $CFG, $PAGE;
        $module = (int)$CFG->version >= 2026100500 ? 'table_presentation_53' : 'table_presentation';
        $PAGE->requires->js_call_amd('local_scriptmanager/' . $module, 'init');
    }

    /**
     * Render a timestamp in the current user's date format.
     *
     * @param int $timestamp Timestamp.
     * @return string
     */
    public static function date(int $timestamp): string {
        return userdate($timestamp, get_string('managerdateformat', 'local_scriptmanager'));
    }

    /**
     * Render the script's publication and enablement state.
     *
     * @param string $label Accessible control label.
     * @param string $icon Native icon name.
     * @param string $tone Visual tone of the notice or control.
     * @return string
     */
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

    /**
     * Report or render the current enablement state.
     *
     * @param bool $enabled Whether future delivery is enabled.
     * @return string
     */
    public static function enabled(bool $enabled): string {
        return self::status(
            get_string($enabled ? 'enabled' : 'disabled', 'core_admin'),
            $enabled ? 'eye' : 'eye-slash',
            $enabled ? 'success' : 'secondary'
        );
    }

    /**
     * Render the available published-version state.
     *
     * @param bool $published Published.
     * @return string
     */
    public static function publication(bool $published): string {
        return self::status(
            get_string($published ? 'scriptpublished' : 'scriptunpublished', 'local_scriptmanager'),
            $published ? 'check-circle' : 'file-o',
            $published ? 'success' : 'secondary'
        );
    }
}
