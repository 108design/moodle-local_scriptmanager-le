<?php
/**
 * Connect native Moodle hooks to the component runtime.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager\local;

use core\hook\output\before_standard_footer_html_generation;
use local_scriptmanager\local\service\factory;

/**
 * Fixed footer output for published, enabled scripts on permitted pages.
 */
final class hook_callbacks {
    /**
     * Add permitted published scripts before the native footer is rendered.
     *
     * @param before_standard_footer_html_generation $hook Native Moodle output hook.
     * @return void
     */
    public static function before_standard_footer_html(before_standard_footer_html_generation $hook): void {
        global $PAGE;
        if (!authority::active()) {
            return;
        }
        $resolver = new context_script_resolver(factory::runtime_script_repository());
        $scripts = $resolver->resolve($PAGE);
        if (!$scripts) {
            return;
        }
        $context = $resolver->context_data($PAGE);
        $html = \html_writer::tag('script', '', [
            'src' => (new \moodle_url('/local/scriptmanager/js/script_runtime.js'))->out(false),
            'data-page-type' => $context['pageType'], 'data-course-id' => $context['courseId'],
            'data-category-id' => $context['categoryId'],
        ]);
        foreach ($scripts as $script) {
            $html .= \html_writer::tag('script', '', ['src' => (new \moodle_url('/local/scriptmanager/script.php', [
                'id' => $script->id, 'version' => $script->versionid, 'v' => $script->versionnumber,
            ]))->out(false)]);
        }
        $hook->add_html($html);
    }
}
