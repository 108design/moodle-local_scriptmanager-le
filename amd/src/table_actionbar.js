// This file is part of Moodle - https://moodle.org/
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/table_actionbar.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */
// Copyright (C) 2026 Andreas Giesen. See the consuming plugin's LICENSE.md.

const SELECTORS = '.local-scriptmanager-editor-actions';

const barsFor = table => Array.from(table.querySelectorAll(SELECTORS)).filter(bar =>
    !bar.closest('[data-table-actionbar-ignore]'));

const actionItems = bar => Array.from(bar.children).filter(item =>
    !item.matches('[data-table-actionbar-toggle], [data-table-actionbar-overflow]'));

export const init = config => {
    if (document.body.dataset.sharedTableActionbar === '1') return;
    document.body.dataset.sharedTableActionbar = '1';
    const labels = {more: config?.more || 'More actions'};
    let open = null;
    let scheduled = false;
    let resizeObserver = null;
    const portal = document.createElement('div');
    portal.className = 'shared-table-actionbar-portal';
    portal.hidden = true;
    document.body.append(portal);

    const close = (restoreFocus = false) => {
        if (!open) return;
        open.overflow.hidden = true;
        open.placeholder.replaceWith(open.overflow);
        open.toggle.setAttribute('aria-expanded', 'false');
        portal.hidden = true;
        portal.replaceChildren();
        if (restoreFocus) open.toggle.focus();
        open = null;
    };

    const position = toggle => {
        const rect = toggle.getBoundingClientRect();
        const top = Math.min(rect.bottom + 6, window.innerHeight - portal.offsetHeight - 8);
        const left = Math.min(rect.right - portal.offsetWidth, window.innerWidth - portal.offsetWidth - 8);
        portal.style.top = `${Math.max(8, top)}px`;
        portal.style.left = `${Math.max(8, left)}px`;
    };

    const prepare = bar => {
        if (bar.dataset.tableActionbarReady === '1') return;
        bar.dataset.tableActionbarReady = '1';
        const overflow = document.createElement('span');
        overflow.className = 'shared-table-actionbar-overflow';
        overflow.dataset.tableActionbarOverflow = '1';
        overflow.hidden = true;
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'shared-table-actionbar-toggle';
        toggle.dataset.tableActionbarToggle = '1';
        toggle.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', labels.more);
        toggle.title = labels.more;
        toggle.innerHTML = '<i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>'
            + `<span class="sr-only">${labels.more.replace(/[&<>"']/g, char => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
            })[char])}</span>`;
        bar.append(overflow, toggle);
        resizeObserver?.observe(bar.closest('.table-responsive') || bar.closest('table'));
        toggle.addEventListener('click', event => {
            event.stopPropagation();
            if (open?.bar === bar) { close(); return; }
            close();
            if (!overflow.children.length) return;
            const placeholder = document.createComment('table actionbar overflow');
            overflow.replaceWith(placeholder);
            portal.append(overflow);
            overflow.hidden = false;
            portal.hidden = false;
            position(toggle);
            toggle.setAttribute('aria-expanded', 'true');
            open = {bar, overflow, placeholder, toggle};
            portal.querySelector('a, button')?.focus();
        });
    };

    const restore = bar => {
        prepare(bar);
        const overflow = bar.querySelector('[data-table-actionbar-overflow]');
        const toggle = bar.querySelector('[data-table-actionbar-toggle]');
        if (!overflow || !toggle) return;
        while (overflow.firstChild) bar.insertBefore(overflow.firstChild, overflow);
        toggle.hidden = true;
        overflow.hidden = true;
        bar.classList.remove('is-table-actionbar-stacked', 'is-table-actionbar-stacked-four',
            'is-table-actionbar-stacked-five');
    };

    const overflows = table => {
        const wrapper = table.closest('.table-responsive') || table.parentElement;
        return Boolean(wrapper && table.scrollWidth > wrapper.clientWidth + 1);
    };

    const applyCap = (bars, cap) => bars.forEach(bar => {
        const overflow = bar.querySelector('[data-table-actionbar-overflow]');
        const toggle = bar.querySelector('[data-table-actionbar-toggle]');
        const items = actionItems(bar);
        while (items.length > cap) overflow.prepend(items.pop());
        const hasOverflow = overflow.children.length > 0;
        overflow.hidden = true;
        toggle.hidden = !hasOverflow;
    });

    const measureTable = table => {
        if (!table.isConnected) return;
        if (open && open.bar.closest('table') === table) close();
        const bars = barsFor(table);
        if (!bars.length) return;
        bars.forEach(restore);
        const maximum = Math.max(...bars.map(bar => actionItems(bar).length));
        if (maximum <= 3) return;
        if (maximum === 4 || maximum === 5) {
            bars.forEach(bar => {
                const count = actionItems(bar).length;
                if (count === 4 || count === 5) {
                    bar.classList.add('is-table-actionbar-stacked',
                        `is-table-actionbar-stacked-${count === 4 ? 'four' : 'five'}`);
                }
            });
            if (!overflows(table)) return;
            bars.forEach(bar => bar.classList.remove('is-table-actionbar-stacked',
                'is-table-actionbar-stacked-four', 'is-table-actionbar-stacked-five'));
        }
        for (let cap = maximum - 1; cap >= 2 && overflows(table); cap--) applyCap(bars, cap);
    };

    const measureAll = () => {
        scheduled = false;
        document.querySelectorAll('table').forEach(table => {
            if (barsFor(table).length) measureTable(table);
        });
    };
    const schedule = () => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(measureAll);
    };

    document.addEventListener('click', event => {
        if (open && !portal.contains(event.target) && event.target !== open.toggle) close();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && open) { event.preventDefault(); close(true); }
    });
    window.addEventListener('resize', () => { close(); schedule(); });
    if (typeof ResizeObserver === 'function') {
        resizeObserver = new ResizeObserver(schedule);
        resizeObserver.observe(document.body);
    }
    new MutationObserver(records => {
        const hasNewBars = records.some(record => Array.from(record.addedNodes).some(node =>
            node.nodeType === Node.ELEMENT_NODE
            && (node.matches?.(SELECTORS) || node.querySelector?.(SELECTORS))));
        if (hasNewBars) schedule();
    }).observe(document.body, {childList: true, subtree: true});
    schedule();
};
