// This file is part of Moodle - https://moodle.org/
/* This file is part of a 108design source-available software product. Use and modification are permitted only under the Software License included with this distribution. See LICENSE.md for the full terms. @package local_scriptmanager @copyright 2026 Andreas Giesen <andreas@108design.com> @license 108design source-available software license; see LICENSE.md */
import {createInit} from './source-editor-core.js';
import {javascript} from '@codemirror/lang-javascript';

define('local_scriptmanager/source_editor', [], () => ({
    init: createInit('scriptmanagerSourceEditor', {javascript}, 'javascript'),
}));
