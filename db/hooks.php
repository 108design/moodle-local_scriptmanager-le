<?php
/**
 * Native hook registrations for Script Manager.
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
defined('MOODLE_INTERNAL') || die();
$callbacks = [
        [
        'hook' => \core\hook\output\before_standard_footer_html_generation::class,
         'callback' => [ \local_scriptmanager\local\hook_callbacks::class, 'before_standard_footer_html' ],
        ],
    ];

// Decorative product identity is restricted to the native administration surfaces.
$callbacks[] = [
    'hook' => \core\hook\output\before_standard_head_html_generation::class,
    'callback' => [\local_scriptmanager\local\admin_branding::class, 'head'],
];
$callbacks[] = [
    'hook' => \core\hook\output\before_http_headers::class,
    'callback' => [\local_scriptmanager\local\admin_branding::class, 'prepare'],
];

// Independent signed update-package guard.
$callbacks[] = [
    'hook' => \core\hook\after_config::class,
    'callback' => [\local_scriptmanager\local\update_guard::class, 'after_config'],
    'priority' => 1002,
];
