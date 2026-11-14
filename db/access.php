<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Capability definitions for Script Manager.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */

// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>.
//
// Use and modification are permitted only under the Software License included
// with this distribution. See LICENSE.md for the full terms.
defined('MOODLE_INTERNAL') || die();
$capabilities = [
        'local/scriptmanager:manage' => [
        'riskbitmask' => RISK_XSS | RISK_CONFIG,
         'captype' => 'write',
         'contextlevel' => CONTEXT_SYSTEM,
         'archetypes' => [ ],
        ],
    ];
