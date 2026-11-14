// This file is part of Moodle - https://moodle.org/
/**
 * File documentation for js/script_runtime.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// Licensed under the Software License in LICENSE.md.
(function() {
    'use strict';

    const runtimeScript = document.currentScript;
    const scripts = new Map();
    const context = {
        root: document,
        pageId: 0,
        pageType: runtimeScript?.dataset.pageType || '',
        courseId: Number(runtimeScript?.dataset.courseId) || 0,
        categoryId: Number(runtimeScript?.dataset.categoryId) || 0,
    };
    const execute = (item, root = document) => {
        try {
            item.run({...context, root});
        } catch (error) {
            window.console.error(`Managed script "${item.id}" failed.`, error);
        }
    };
    const register = item => {
        scripts.set(item.id, item);
        execute(item);
    };
    window.localScriptManagerRuntime = {register};
    (window.localScriptManagerQueue || []).splice(0).forEach(register);
    const updated = event => {
        const nodes = Array.from(event.detail?.nodes || []);
        const roots = nodes.length ? [...new Set(nodes)] : [event.target || document];
        roots.forEach(root => scripts.forEach(item => execute(item, root)));
    };
    // Native Moodle filter events are available across the supported Moodle branches.
    document.addEventListener('core_filters/contentUpdated', updated);
    // Preserve callers that dispatch the earlier DOM event directly.
    document.addEventListener('filter-content-updated', updated);
}());
