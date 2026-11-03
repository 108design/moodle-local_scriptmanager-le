<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_scriptmanager\output;

/** Read-only customer help, limited to the current authoring context. */
final class context_help {
    private const CONTEXTS = [
        'scripts' => ['scriptbasics', 'scriptpublication', 'scriptexecution', 'scriptrevisions', 'scriptidentity', 'moduleownership'],
    ];

    private static ?string $context = null;

    /** Shared preview renderers must not add help to unregistered chooser contexts. */
    public static function registered(string $context): bool {
        return self::$context === $context;
    }

    public static function prepare(string $context): void {
        global $CFG, $PAGE;
        self::topics($context);
        self::$context = $context;
        $PAGE->requires->css(new \moodle_url('/local/scriptmanager/styles/context_help.css', [
            'rev' => get_config('local_scriptmanager', 'version'),
        ]));
        $module = (int)$CFG->version < 2025041400 ? 'context_help_legacy' : 'context_help';
        $PAGE->requires->js_call_amd('local_scriptmanager/context_help_form', 'init');
        $PAGE->requires->js_call_amd('local_scriptmanager/' . $module, 'init', [[
            'topic' => optional_param('scriptmanagerhelp', '', PARAM_ALPHA),
        ]]);
    }

    private static function topics(string $context): array {
        if (!isset(self::CONTEXTS[$context])) {
            throw new \coding_exception('Unknown help context.');
        }
        return self::CONTEXTS[$context];
    }

    /** Keep Moodle's help popover, using an information icon for our own context help. */
    public static function icon(string $topic, string $context = 'scripts'): string {
        global $CFG, $PAGE;
        if (!in_array($topic, self::topics($context), true)) {
            throw new \coding_exception('Unknown help topic.');
        }
        $url = new \moodle_url($PAGE->url);
        $url->param('scriptmanagerhelp', $topic);
        $help = new \help_icon('pagehelp_' . $topic, 'local_scriptmanager', $url->out(false));
        // Form labels and dynamic source configuration may request help before output starts.
        $renderer = $PAGE->get_renderer('core');
        $data = (array)$help->export_for_template($renderer);
        $data['completedoclink'] = \html_writer::link($url, get_string('pagehelp_more', 'local_scriptmanager'));
        $data['legacy'] = (int)$CFG->version < 2025041400;
        $data['infoicon'] = (int)$CFG->version < 2026100500 ? 'i/info' : 'i/circleinfo';
        return $renderer->render_from_template('local_scriptmanager/context_help_icon', $data);
    }

    /** Called by Moodle's navbar callback only after a permitted page registers its help. */
    public static function navbar(\renderer_base $renderer): string {
        global $CFG;
        if (self::$context === null) {
            return '';
        }
        $key = self::$context === 'pages' ? 'pagehelp_title' : 'pagehelp_' . self::$context . '_title';
        $label = get_string($key, 'local_scriptmanager');
        return \html_writer::tag('button', $renderer->pix_icon((int)$CFG->version < 2026100500 ? 'i/info' : 'i/circleinfo', ''), [
            'type' => 'button',
            'class' => 'nav-link icon-no-margin local-scriptmanager-help-navbar',
            'data-scriptmanager-help-open' => '', 'data-scriptmanager-help-context' => self::$context,
            'aria-label' => $label, 'title' => $label,
            'aria-controls' => 'local-scriptmanager-context-help', 'aria-expanded' => 'false',
        ]);
    }

    public static function render(string $context = 'scripts'): string {
        global $CFG, $PAGE;
        $key = $context === 'pages' ? 'pagehelp_title' : 'pagehelp_' . $context . '_title';
        $title = get_string($key, 'local_scriptmanager');
        $close = \html_writer::tag('button', \html_writer::span('&#215;', '', ['aria-hidden' => 'true']), [
            'type' => 'button', 'class' => 'btn btn-secondary btn-sm local-scriptmanager-help-close',
            'aria-label' => get_string('closebuttontitle', 'moodle'), 'data-scriptmanager-help-close' => '1',
        ]);
        $options = '';
        $articles = '';
        foreach (self::topics($context) as $topic) {
            $label = get_string('pagehelp_' . $topic, 'local_scriptmanager');
            $options .= \html_writer::tag('option', $label, ['value' => $topic]);
            $articles .= \html_writer::tag('article', \html_writer::tag('h3', $label, ['class' => 'h5', 'tabindex' => '-1'])
                . get_string('pagehelp_' . $topic . '_article', 'local_scriptmanager'), [
                    'data-scriptmanager-help-article' => $topic, 'hidden' => 'hidden',
                ]);
        }
        $choice = \html_writer::tag('label', get_string('contexthelptopics', 'local_scriptmanager'), [
            'for' => 'local-scriptmanager-help-topic', 'class' => 'form-label',
        ]) . \html_writer::tag('select', $options, [
            'id' => 'local-scriptmanager-help-topic', 'class' => 'form-select custom-select',
        ]);
        // Shared help-panel identity: no link or action; close remains beside the topic selector.
        $brand = \html_writer::tag('span', \html_writer::empty_tag('img', [
            'src' => $PAGE->get_renderer('core')->image_url('helpbrand', 'local_scriptmanager')->out(false),
            'alt' => '', 'width' => 25, 'height' => 24,
        ]), [
            'class' => 'local-scriptmanager-help-brand', 'role' => 'img', 'aria-label' => '108design',
            'title' => '108design', 'tabindex' => '0', 'data-toggle' => 'tooltip', 'data-bs-toggle' => 'tooltip',
        ]);
        $header = \html_writer::tag('header', \html_writer::tag('h2', $title, [
            'id' => 'local-scriptmanager-context-help-title', 'class' => 'sr-only visually-hidden', 'tabindex' => '-1',
        ]) . \html_writer::div($choice, 'local-scriptmanager-help-choices') . $brand . $close,
            ['class' => 'local-scriptmanager-help-header local-scriptmanager-help-header-branded']);
        return \html_writer::tag('aside', $header
            . \html_writer::div($articles, 'local-scriptmanager-help-content', ['tabindex' => '0']), [
                'id' => 'local-scriptmanager-context-help', 'class' => 'local-scriptmanager-context-help',
                'aria-labelledby' => 'local-scriptmanager-context-help-title', 'hidden' => 'hidden',
                'data-scriptmanager-help-url' => $PAGE->url->out(false),
            ]);
    }
}
