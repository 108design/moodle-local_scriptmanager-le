<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Authorised, read-only revision chooser and escaped code diff.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_scriptmanager\output;

use local_scriptmanager\local\script_revision_diff;

/**
 * Authorised, read-only revision chooser and escaped code diff.
 */
final class script_revision_comparison {
    /**
     * Render the scoped comparison of two published script versions.
     *
     * @param array $versions Stored revisions to compare.
     * @param int $scriptid Managed script ID.
     * @param \moodle_url $url Local or validated destination URL.
     * @param int $from Source revision or position.
     * @param int $to Target revision or position.
     * @return string
     */
    public static function render(array $versions, int $scriptid, \moodle_url $url, int $from, int $to): string {
        $pair = script_revision_diff::select($versions, $scriptid, $from, $to);
        if (!$pair) {
            return \html_writer::tag(
                'p',
                get_string('scriptdiffneedtwo', 'local_scriptmanager'),
                [ 'class' => 'text-muted' ]
            );
        }
        [$before, $after] = $pair;
        $fields = \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $scriptid]);
        foreach (['from' => $before, 'to' => $after] as $name => $selected) {
            $options = [];
            foreach ($versions as $v) {
                if ((int)$v->scriptid === $scriptid) {
                    $options[$v->id] = get_string('scriptdiffrevision', 'local_scriptmanager', $v->versionnumber);
                }
            }
            $fields .= \html_writer::div(\html_writer::tag(
                'label',
                get_string('scriptdiff' . $name, 'local_scriptmanager'),
                ['for' => 'scriptdiff-' . $name, 'class' => 'form-label']
            )
                . \html_writer::select($options, $name, $selected->id, false, ['id' => 'scriptdiff-' . $name,
                    'class' => 'form-select custom-select']), 'local-scriptmanager-script-diff-choice');
        }
        $fields .= \html_writer::tag(
            'button',
            get_string('scriptdiffcompare', 'local_scriptmanager'),
            ['type' => 'submit', 'class' => 'btn btn-outline-secondary align-self-end']
        );
        $html = \html_writer::tag('h3', get_string('scriptdiffheading', 'local_scriptmanager'), ['class' => 'h5'])
            . \html_writer::tag('form', $fields, ['method' => 'get', 'action' => $url->out_omit_querystring(),
                'class' => 'd-flex flex-wrap align-items-end local-scriptmanager-gap-3 mb-3']);
        $hashes = '';
        foreach ([$before, $after] as $v) {
            $hashes .= \html_writer::tag(
                'dt',
                get_string('scriptdiffrevision', 'local_scriptmanager', $v->versionnumber)
            )
                . \html_writer::tag('dd', \html_writer::tag('code', hash('sha256', (string)$v->code)));
        }
        $html .= \html_writer::tag(
            'details',
            \html_writer::tag('summary', 'SHA-256')

            . \html_writer::tag('dl', $hashes, [ 'class' => 'small mt-2' ]),
            [ 'class' => 'mb-3 local-scriptmanager-script-hashes' ]
        );
        $codeversions = $before->id === $after->id ? [$after] : [$before, $after];
        foreach ($codeversions as $v) {
            $html .= \html_writer::tag('details', \html_writer::tag(
                'summary',
                get_string('scriptdiffsource', 'local_scriptmanager', $v->versionnumber)
            )
                . \html_writer::tag(
                    'pre',
                    s((string)$v->code),
                    ['class' => 'local-scriptmanager-script-diff-code mt-2', 'tabindex' => '0']
                ), ['class' => 'mb-3']);
        }
        // Bound array allocation for unusually newline-dense trusted source.
        if (substr_count((string)$before->code, "\n") > 25000 || substr_count((string)$after->code, "\n") > 25000) {
            $html .= \html_writer::tag(
                'p',
                get_string('scriptdiffdense', 'local_scriptmanager'),
                [ 'class' => 'text-muted' ]
            );
            foreach ([$before, $after] as $version) {
                $html .= \html_writer::tag(
                    'h4',
                    get_string('scriptdiffrevision', 'local_scriptmanager', $version->versionnumber),
                    [ 'class' => 'h6' ]
                )

                . \html_writer::tag(
                    'pre',
                    s($version->code),
                    [ 'class' => 'local-scriptmanager-script-diff-code', 'tabindex' => '0' ]
                );
            }
            return \html_writer::div(
                $html,
                'local-scriptmanager-script-comparison mt-4 mb-4',
                [ 'id' => 'script-comparison' ]
            );
        }
        $diff = script_revision_diff::operations((string)$before->code, (string)$after->code);
        $hunks = script_revision_diff::hunks($diff['operations']);
        if (!$hunks) {
            $html .= \html_writer::tag(
                'p',
                get_string('scriptdiffunchanged', 'local_scriptmanager'),
                [ 'class' => 'text-muted' ]
            );
        } else {
            $html .= \html_writer::tag(
                'p',
                get_string('scriptdifflegend', 'local_scriptmanager'),
                [ 'class' => 'small' ]
            );
            if ($diff['coarse']) {
                $html .= \html_writer::tag(
                    'p',
                    get_string('scriptdiffblock', 'local_scriptmanager'),
                    [ 'class' => 'small text-muted' ]
                );
            }
            $code = '';
            foreach ($hunks as $hunk) {
                $oldcount = count(array_filter($hunk, static fn($r) => $r['type'] !== 'add'));
                $newcount = count(array_filter($hunk, static fn($r) => $r['type'] !== 'remove'));
                $code .= \html_writer::span('@@ -' . $hunk[0]['old'] . ',' . $oldcount . ' +'
                    . $hunk[0]['new'] . ',' . $newcount . " @@\n", 'local-scriptmanager-diff-range');
                $groups = [];
                foreach ($hunk as $row) {
                    $symbol = ['equal' => ' ', 'remove' => '−', 'add' => '+'][$row['type']];
                    $last = count($groups) - 1;
                    $line = $symbol . ' ' . $row['text'] . "\n";
                    if ($last >= 0 && $groups[$last][0] === $row['type']) {
                        $groups[$last][1] .= $line;
                    } else {
                        $groups[] = [$row['type'], $line];
                    }
                }
                foreach ($groups as [$type, $text]) {
                    $code .= \html_writer::span(s($text), 'local-scriptmanager-diff-' . $type);
                }
            }
            $html .= \html_writer::tag(
                'pre',
                $code,
                [
                'class' => 'local-scriptmanager-script-diff-code',
                'tabindex' => '0',

                'aria-label' => get_string('scriptdiffheading', 'local_scriptmanager'),
                ]
            );
        }
        return \html_writer::div(
            $html,
            'local-scriptmanager-script-comparison mt-4 mb-4',
            [ 'id' => 'script-comparison' ]
        );
    }
}
