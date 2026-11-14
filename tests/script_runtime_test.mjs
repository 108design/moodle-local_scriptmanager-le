// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import vm from 'node:vm';
const handlers = new Map();
const calls = []; const errors = [];
const document = {
    currentScript: {dataset: {pageType:'course-view-topics',courseId:'2',categoryId:'3'}},
    addEventListener: (type, fn) => handlers.set(type, fn),
};
const window = {console:{error:e=>errors.push(e)},localScriptManagerQueue:[
    {id:'alpha',run:context=>calls.push({id:'alpha',...context})},
    {id:'beta',run:context=>calls.push({id:'beta',...context})},
]};
// The standard event must work even before optional legacy YUI globals exist.
vm.runInNewContext(await fs.readFile(new URL('../js/script_runtime.js',import.meta.url),'utf8'),{window,document});
assert.equal(calls.length,2);
assert.equal(calls[0].courseId,2);
const first={synthetic:'first'}, second={synthetic:'second'};
handlers.get('core_filters/contentUpdated')({target:document,detail:{nodes:[first,second,first]}});
assert.equal(calls.length,6);
assert.equal(calls.filter(c=>c.root===first).length,2);
assert.equal(calls.filter(c=>c.root===second).length,2);
handlers.get('filter-content-updated')({target:first});
assert.equal(calls.length,8);
window.localScriptManagerRuntime.register({id:'error',run:()=>{throw Error('Synthetic synchronous error');}});
assert.equal(errors.length,1);
assert.equal(window.localScriptManagerQueue.length,0);
console.log('Runtime event, context, root, queue and synchronous error checks passed.');
