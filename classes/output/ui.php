<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. See LICENSE.md for the full terms.
namespace local_scriptmanager\output;

defined('MOODLE_INTERNAL') || die();

/** Script manager-owned UI primitives; no dependency on Storefront or Frontpage. */
final class ui {
    private static function icon(string $icon): string {
        global $OUTPUT;
        if (str_starts_with($icon, 'fa-')) {
            return \html_writer::tag('i', '', ['class' => 'fa ' . $icon . ' icon', 'aria-hidden' => 'true']);
        }
        return $OUTPUT->pix_icon($icon, '');
    }

    public static function icon_action(\moodle_url $url, string $label, string $icon, string $classes = ''): string {
        return \html_writer::link($url, self::icon($icon) . \html_writer::span($label, 'sr-only'), [
            'class' => trim('local-scriptmanager-icon-button ' . $classes),
            'aria-label' => $label,
            'title' => $label,
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
        ]);
    }

    public static function icon_modal_action(string $modalid, string $label, string $icon,
            string $classes = ''): string {
        $button = \html_writer::tag('button', self::icon($icon) . \html_writer::span($label, 'sr-only'), [
            'type' => 'button',
            'class' => trim('local-scriptmanager-icon-button ' . $classes),
            'aria-label' => $label,
            'title' => $label,
            'data-action' => 'local-scriptmanager-open-confirmation',
            'data-modal-id' => $modalid,
            'aria-controls' => $modalid,
            'aria-haspopup' => 'dialog',
        ]);
        return \html_writer::span($button, 'd-inline-flex', [
            'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => $label,
        ]);
    }

    public static function icon_post_action(\moodle_url $url, string $label, string $icon): string {
        $button = \html_writer::tag('button', self::icon($icon) . \html_writer::span($label, 'sr-only'), [
            'type' => 'submit', 'class' => 'local-scriptmanager-icon-button', 'aria-label' => $label,
            'title' => $label, 'data-toggle' => 'tooltip', 'data-placement' => 'top',
        ]);
        return \html_writer::tag('form',
            \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()])
            . $button,
            ['method' => 'post', 'action' => $url->out(false), 'class' => 'm-0 d-inline-flex']);
    }

    public static function confirmation_modal(string $id, string $title, string $message,
            \moodle_url $url, string $confirmlabel): string {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_scriptmanager/confirmation_modal', 'init');
        $fields = '';
        foreach ($url->params() as $name => $value) {
            $fields .= \html_writer::empty_tag('input', [
                'type' => 'hidden', 'name' => $name, 'value' => $value,
            ]);
        }
        $form = \html_writer::tag('form', $fields . \html_writer::tag('button', $confirmlabel, [
            'type' => 'submit', 'class' => 'btn btn-danger',
        ]), ['method' => 'post', 'action' => $url->out_omit_querystring(), 'class' => 'm-0']);
        $content = \html_writer::div($title, '', ['data-region' => 'confirmation-title'])
            . \html_writer::div(\html_writer::tag('p', $message, ['class' => 'mb-0']), '', [
                'data-region' => 'confirmation-body',
            ])
            . \html_writer::div(\html_writer::tag('button', get_string('cancel'), [
                'type' => 'button', 'class' => 'btn btn-secondary',
                'data-action' => 'local-scriptmanager-cancel-confirmation',
            ]) . $form, '', ['data-region' => 'confirmation-footer']);
        return \html_writer::tag('div', $content, [
            'id' => $id,
            'data-region' => 'local-scriptmanager-confirmation',
            'hidden' => 'hidden',
        ]);
    }

    public static function actionbar(array $actions): string {
        return \html_writer::div(implode('', array_filter($actions)), 'local-scriptmanager-actionbar');
    }

    public static function table_actions(array $actions): string {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_scriptmanager/table_actionbar', 'init', [[
            'more' => get_string('actionbarmore', 'local_scriptmanager'),
        ]]);
        return \html_writer::div(implode('', array_filter($actions)), 'local-scriptmanager-editor-actions');
    }

    public static function page_header(string $title, array $actions = []): string {
        return \html_writer::div(
            \html_writer::tag('h2', $title, ['class' => 'mb-0']) . ($actions ? self::actionbar($actions) : ''),
            'local-scriptmanager-page-header d-flex flex-wrap align-items-center justify-content-between '
                . 'local-scriptmanager-gap-3 mb-4');
    }
}
