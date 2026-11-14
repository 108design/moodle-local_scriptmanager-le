// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
import {basicSetup, EditorView} from 'codemirror';
import {EditorState} from '@codemirror/state';

export const createInit = (dataAttribute, languages, fallbackLanguage) => (configOrId, language = fallbackLanguage) => {
    const config = typeof configOrId === 'string'
        ? {textareaId: configOrId, language}
        : configOrId;
    const textarea = document.getElementById(config.textareaId);
    if (!textarea || textarea.dataset[dataAttribute] === 'true') {
        return;
    }
    const container = document.createElement('div');
    container.classList.add('local-scriptmanager-code-editor');
    textarea.insertAdjacentElement('afterend', container);
    const label = document.querySelector(`label[for="${CSS.escape(config.textareaId)}"]`);
    const accessibleLabel = label?.textContent?.trim() || textarea.getAttribute('aria-label') || 'Source code';
    const state = EditorState.create({
        doc: textarea.value,
        extensions: [
            basicSetup,
            EditorState.tabSize.of(2),
            EditorView.lineWrapping,
            EditorView.contentAttributes.of({'aria-label': accessibleLabel, 'aria-multiline': 'true'}),
            EditorView.updateListener.of(update => {
                if (update.docChanged) {
                    textarea.value = update.state.doc.toString();
                    textarea.dispatchEvent(new Event('input', {bubbles: true}));
                }
            }),
            (languages[config.language] || languages[fallbackLanguage])(),
        ],
    });
    const view = new EditorView({state, parent: container});
    textarea.dataset[dataAttribute] = 'true';
    textarea.hidden = true;
    textarea.closest('form')?.addEventListener('reset', () => window.requestAnimationFrame(() => view.dispatch({
        changes: {from: 0, to: view.state.doc.length, insert: textarea.value},
    })));
};
