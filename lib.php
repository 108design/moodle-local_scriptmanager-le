<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for lib.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
defined('MOODLE_INTERNAL') || die();

function local_scriptmanager_render_navbar_output(\renderer_base $renderer): string {
    return \local_scriptmanager\output\context_help::navbar($renderer);
}
