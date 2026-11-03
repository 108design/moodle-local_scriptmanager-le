<?php
/**
 * File documentation for index.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
require_once(__DIR__.'/../../config.php');require_login();require_capability('local/scriptmanager:manage',context_system::instance());redirect(new moodle_url('/local/scriptmanager/scripts.php'));
