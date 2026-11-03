import {createInit} from './source-editor-core.js';
import {javascript} from '@codemirror/lang-javascript';

define('local_scriptmanager/source_editor', [], () => ({
    init: createInit('scriptmanagerSourceEditor', {javascript}, 'javascript'),
}));
