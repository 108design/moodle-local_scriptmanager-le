/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/search_managers.js.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */
// Copyright (C) 2026 Andreas Giesen. See the consuming plugin's LICENSE.md.
import {init as initSearchBar} from './search_bar';

export const init = (modules = []) => {
    document.querySelectorAll('form[data-search-bar]').forEach(form => {
        initSearchBar(form);
        if (form.dataset.searchManagerReady === '1') return;
        form.dataset.searchManagerReady = '1';
        form.addEventListener('search-bar-updated', () => {
            for (const item of modules) {
                require(['local_scriptmanager/' + item.name], module => module.init(...(item.args || [])));
            }
        });
    });
};
