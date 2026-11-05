<?php
/**
 * Render shared native interface controls for the component.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>.
//
// Use and modification are permitted only under the Software License included
// with this distribution. See LICENSE.md for the full terms.
namespace local_scriptmanager\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Script manager-owned UI primitives; no dependency on Storefront or Frontpage.
 */
final class ui {
    /**
     * Render a native Moodle icon.
     *
     * @param string $icon Native icon name.
     * @return string
     */
    private static function icon(string $icon): string {
        global $OUTPUT;
        if (str_starts_with($icon, 'fa-')) {
            return \html_writer::tag('i', '', ['class' => 'fa ' . $icon . ' icon', 'aria-hidden' => 'true']);
        }
        return $OUTPUT->pix_icon($icon, '');
    }

    /**
     * Render an accessible icon link for a native action.
     *
     * @param \moodle_url $url Local or validated destination URL.
     * @param string $label Accessible control label.
     * @param string $icon Native icon name.
     * @param string $classes CSS classes for the generated element.
     * @return string
     */
    public static function icon_action(\moodle_url $url, string $label, string $icon, string $classes = ''): string {
        return \html_writer::link($url, self::icon($icon) . \html_writer::span($label, 'sr-only'), [
            'class' => trim('local-scriptmanager-icon-button ' . $classes),
            'aria-label' => $label,
            'title' => $label,
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
        ]);
    }

    /**
     * Render an icon action that opens the native confirmation dialog.
     *
     * @param string $modalid Modalid.
     * @param string $label Accessible control label.
     * @param string $icon Native icon name.
     * @param string $classes CSS classes for the generated element.
     * @return string
     */
    public static function icon_modal_action(
        string $modalid,
        string $label,
        string $icon,
        string $classes = ''
    ): string {
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

    /**
     * Render a sesskey-protected POST action as an icon control.
     *
     * @param \moodle_url $url Local or validated destination URL.
     * @param string $label Accessible control label.
     * @param string $icon Native icon name.
     * @param string $classes CSS classes for the generated element.
     * @return string
     */
    public static function icon_post_action(
        \moodle_url $url,
        string $label,
        string $icon,
        string $classes = ''
    ): string {
        $button = \html_writer::tag('button', self::icon($icon) . \html_writer::span($label, 'sr-only'), [
            'type' => 'submit', 'class' => trim('local-scriptmanager-icon-button ' . $classes), 'aria-label' => $label,
            'title' => $label, 'data-toggle' => 'tooltip', 'data-placement' => 'top',
        ]);
        return \html_writer::tag(
            'form',
            \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()])
            . $button,
            ['method' => 'post', 'action' => $url->out(false), 'class' => 'm-0 d-inline-flex']
        );
    }

    /**
     * Render the native confirmation dialog for the action.
     *
     * @param string $id Record ID.
     * @param string $title Display title.
     * @param string $message Message text.
     * @param \moodle_url $url Local or validated destination URL.
     * @param string $confirmlabel Confirmlabel.
     * @return string
     */
    public static function confirmation_modal(
        string $id,
        string $title,
        string $message,
        \moodle_url $url,
        string $confirmlabel
    ): string {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_scriptmanager/confirmation_modal', 'init');
        $fields = '';
        foreach ($url->params() as $name => $value) {
            $fields .= \html_writer::empty_tag('input', [ 'type' => 'hidden', 'name' => $name, 'value' => $value ]);
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

    /**
     * Group related native page actions.
     *
     * @param array $actions Action definitions for this view.
     * @return string
     */
    public static function actionbar(array $actions): string {
        return \html_writer::div(implode('', array_filter($actions)), 'local-scriptmanager-actionbar');
    }

    /**
     * Group the actions for one table row.
     *
     * @param array $actions Action definitions for this view.
     * @return string
     */
    public static function table_actions(array $actions): string {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_scriptmanager/table_actionbar', 'init', [[
            'more' => get_string('actionbarmore', 'local_scriptmanager'),
        ]]);
        return \html_writer::div(implode('', array_filter($actions)), 'local-scriptmanager-editor-actions');
    }

    /**
     * Render the component page heading and its action controls.
     *
     * @param string $title Display title.
     * @param array $actions Action definitions for this view.
     * @return string
     */
    public static function page_header(string $title, array $actions = []): string {
        return \html_writer::div(
            \html_writer::tag('h2', $title, ['class' => 'mb-0']) . ($actions ? self::actionbar($actions) : ''),
            'local-scriptmanager-page-header d-flex flex-wrap align-items-center justify-content-between '
            . 'local-scriptmanager-gap-3 mb-4'
        );
    }
}
