// This file is part of Moodle - https://moodle.org/
/**
 * File documentation for amd/src/script_runtime.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// Licensed under the Software License in LICENSE.md.
const scripts = new Map();
let context = null;

const execute = (item, root = document) => {
    try {
        item.run({...context, root});
    } catch (error) {
        window.console.error(`Managed script "${item.id}" failed.`, error);
    }
};

/** Register published code, including when it arrives before initialisation. */
export const register = item => {
    scripts.set(item.id, item);
    if (context) {
        execute(item);
    }
};

/** Initialise the page context once; editor/history exclusion remains server-side. */
export const init = pageContext => {
    if (context) {
        return;
    }
    context = {
        pageId: 0,
        pageType: pageContext.pageType || '',
        courseId: Number(pageContext.courseId) || 0,
        categoryId: Number(pageContext.categoryId) || 0,
    };
    scripts.forEach(item => execute(item));
    const updated = event => {
        const nodes = Array.from(event.detail?.nodes || []);
        const roots = nodes.length ? [...new Set(nodes)] : [event.target || document];
        roots.forEach(root => scripts.forEach(item => execute(item, root)));
    };
    document.addEventListener('core_filters/contentUpdated', updated);
    document.addEventListener('filter-content-updated', updated);
};
