<?php
/**
 * Installation callbacks for Script Manager.
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

/**
 * Initialise persistent component state for Script Manager.
 *
 * @return void
 */
function xmldb_local_scriptmanager_install(): void {
    \local_scriptmanager\local\module_authority::initialise('scriptmanager');
}
