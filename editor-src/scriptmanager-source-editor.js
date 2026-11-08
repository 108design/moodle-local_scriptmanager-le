// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
import {createInit} from './source-editor-core.js';
import {javascript} from '@codemirror/lang-javascript';

define('local_scriptmanager/source_editor', [], () => ({
    init: createInit('scriptmanagerSourceEditor', {javascript}, 'javascript'),
}));
