// This file is part of Moodle - https://moodle.org/
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/table_presentation_53.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */
// Copyright (C) 2026 Andreas Giesen. See the consuming plugin's LICENSE.md.
import * as Bootstrap from 'bootstrap';
import $ from 'jquery';

const selector = '.cms-table-status';

export const init = () => {
    if (document.body.dataset.sharedTablePresentation === '1') return;
    document.body.dataset.sharedTablePresentation = '1';
    const owned = new Map();
    const show = event => {
        const node = event.target instanceof Element ? event.target.closest(selector) : null;
        if (!node || (event.type === 'pointerover' && event.pointerType === 'touch')) return;
        let instance = owned.get(node);
        if (!instance) {
            if (Bootstrap.Tooltip.getInstance?.(node) || $(node).data('bs.tooltip')
                    || node.hasAttribute('data-original-title') || node.hasAttribute('data-bs-original-title')) return;
            const title = node.getAttribute('title') || node.getAttribute('aria-label');
            if (!title) return;
            instance = new Bootstrap.Tooltip(node, {
                title, container: 'body', placement: 'top', trigger: 'hover focus', html: false, animation: false,
            });
            owned.set(node, instance);
        }
        instance.show();
    };
    document.addEventListener('pointerover', show);
    document.addEventListener('focusin', show);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') owned.forEach(instance => instance.hide());
    });
    const cleanup = new MutationObserver(() => {
        owned.forEach((instance, node) => {
            if (!node.isConnected) { instance.dispose(); owned.delete(node); }
        });
    });
    cleanup.observe(document.body, {childList: true, subtree: true});
    window.addEventListener('pagehide', () => {
        cleanup.disconnect();
        owned.forEach(instance => instance.dispose());
        owned.clear();
    });
};
