// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
import {createInit} from './source-editor-core.js';
import {javascript} from '@codemirror/lang-javascript';

define('local_scriptmanager/source_editor', [], () => ({
    init: createInit('scriptmanagerSourceEditor', {javascript}, 'javascript'),
}));
