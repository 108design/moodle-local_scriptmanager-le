<?php
/**
 * Native script entry point for Script Manager.
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
define('NO_MOODLE_COOKIES', true);
require_once(__DIR__ . '/../../config.php');

if (!\local_scriptmanager\local\authority::active()) {
    http_response_code(404);
    exit;
}

$scriptid = required_param('id', PARAM_INT);
$versionid = required_param('version', PARAM_INT);
$sql = 'SELECT v.*, s.identifier FROM {local_scriptmanager_scriptver} v
          JOIN {local_scriptmanager_script} s ON s.id = v.scriptid
         WHERE v.id = :versionid AND v.scriptid = :scriptid AND s.enabled = 1';
$version = $DB->get_record_sql($sql, ['versionid' => $versionid, 'scriptid' => $scriptid]);
if (!$version) {
    http_response_code(404);
    exit;
}
header('Content-Type: application/javascript; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=31536000, immutable');
$identifier = json_encode($version->identifier, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "(function(){'use strict';const item={id:" . $identifier . ",run:function(context){\n";
echo $version->code;
echo "\n}};if(window.localScriptManagerRuntime){window.localScriptManagerRuntime.register(item);}" .
    "else{(window.localScriptManagerQueue=window.localScriptManagerQueue||[]).push(item);}})();";
