<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Native Moodle callbacks for Script Manager.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
defined('MOODLE_INTERNAL') || die();

/**
 * Return the component control for the native navigation bar.
 *
 * @param \renderer_base $renderer Native renderer used for output.
 * @return string
 */
function local_scriptmanager_render_navbar_output(\renderer_base $renderer): string {
    return \local_scriptmanager\output\context_help::navbar($renderer);
}
