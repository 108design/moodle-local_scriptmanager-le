// This file is part of Moodle - https://moodle.org/
/**
 * File documentation for amd/src/confirmation_modal.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// Licensed under the Software License in LICENSE.md.

import Modal from 'core/modal';

const OPEN_ACTION = 'local-scriptmanager-open-confirmation';
const CANCEL_ACTION = 'local-scriptmanager-cancel-confirmation';

/** Submit the protected fallback form after a native confirmation. */
const fallback = (source, trigger) => {
    const title = source.querySelector('[data-region="confirmation-title"]')?.textContent?.trim() || '';
    const message = source.querySelector('[data-region="confirmation-body"]')?.textContent?.trim() || '';
    if (window.confirm([title, message].filter(Boolean).join('\n\n'))) {
        const form = source.querySelector('form');
        if (form?.requestSubmit) {
            form.requestSubmit();
        } else {
            form?.submit();
        }
    } else {
        trigger.focus();
    }
};

/** Open one server-rendered confirmation through Moodle's version-stable Modal API. */
const open = async trigger => {
    const source = document.getElementById(trigger.dataset.modalId || '');
    if (!source || source.dataset.opening === 'true') {
        return;
    }

    source.dataset.opening = 'true';
    trigger.disabled = true;
    try {
        const modal = await Modal.create({
            title: source.querySelector('[data-region="confirmation-title"]')?.innerHTML || '',
            body: source.querySelector('[data-region="confirmation-body"]')?.innerHTML || '',
            footer: source.querySelector('[data-region="confirmation-footer"]')?.innerHTML || '',
            isVerticallyCentered: true,
            removeOnClose: true,
            returnElement: trigger,
            show: true,
        });
        const root = modal.getRoot()?.[0];
        root?.addEventListener('click', event => {
            if (event.target.closest(`[data-action="${CANCEL_ACTION}"]`)) {
                event.preventDefault();
                modal.hide();
            }
        });
    } catch (error) {
        fallback(source, trigger);
    } finally {
        delete source.dataset.opening;
        trigger.disabled = false;
    }
};

/** Initialise one delegated handler for every current and future confirmation trigger. */
export const init = () => {
    const root = document.documentElement;
    if (root.dataset.localScriptmanagerConfirmationInitialised === 'true') {
        return;
    }
    root.dataset.localScriptmanagerConfirmationInitialised = 'true';
    document.addEventListener('click', event => {
        const trigger = event.target.closest(`[data-action="${OPEN_ACTION}"]`);
        if (!trigger) {
            return;
        }
        event.preventDefault();
        open(trigger);
    });
};
