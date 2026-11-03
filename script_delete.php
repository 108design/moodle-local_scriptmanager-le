<?php
/**
 * File documentation for script_delete.php.
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
// with this distribution. See LICENSE.md for the full terms.
require_once(__DIR__.'/../../config.php');use local_scriptmanager\local\service\factory;$id=required_param('id',PARAM_INT);$confirm=optional_param('confirm',0,PARAM_BOOL);require_login();$c=context_system::instance();require_capability('local/scriptmanager:manage',$c);$returnurl=new moodle_url('/local/scriptmanager/scripts.php');if(!$confirm||!data_submitted()){redirect($returnurl);}require_sesskey();$r=factory::script_repository();$r->get($id);$r->delete($id);redirect($returnurl,get_string('scriptdeleted','local_scriptmanager'));
