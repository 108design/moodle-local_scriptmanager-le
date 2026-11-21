// This file is part of Moodle - https://moodle.org/
/* This file is part of a 108design source-available software product. Use and modification are permitted only under the Software License included with this distribution. See LICENSE.md for the full terms. @package local_scriptmanager @copyright 2026 Andreas Giesen <andreas@108design.com> @license 108design source-available software license; see LICENSE.md */
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import vm from 'node:vm';
const handlers = new Map();
const calls = []; const errors = [];
const document = {
    addEventListener: (type, fn) => handlers.set(type, fn),
};
const window = {console:{error:e=>errors.push(e)}};
let runtime;
const define = (name, dependencies, factory) => {
    assert.equal(name, 'local_scriptmanager/script_runtime');
    assert.deepEqual(Array.from(dependencies), ['exports']);
    runtime = {};
    factory(runtime);
};
// The standard event must work even before optional legacy YUI globals exist.
// Exercise the actual distributable AMD build, not a rewritten source approximation.
vm.runInNewContext(await fs.readFile(new URL('../amd/build/script_runtime.min.js',import.meta.url),'utf8'),
    {window,document,define});
runtime.register({id:'alpha',run:context=>calls.push({id:'alpha',...context})});
runtime.register({id:'beta',run:context=>calls.push({id:'beta',...context})});
assert.equal(calls.length,0, 'Registration waits for the explicit native page context');
runtime.init({pageType:'course-view-topics',courseId:'2',categoryId:'3'});
assert.equal(calls.length,2);
assert.equal(calls[0].courseId,2);
assert.equal(calls[0].categoryId,3);
assert.equal(calls[0].pageType,'course-view-topics');
assert.equal(calls[0].root,document);
runtime.init({pageType:'must-not-reinitialise'});
assert.equal(calls.length,2);
runtime.register({id:'gamma',run:context=>calls.push({id:'gamma',...context})});
assert.equal(calls.length,3);
const first={synthetic:'first'}, second={synthetic:'second'};
handlers.get('core_filters/contentUpdated')({target:document,detail:{nodes:[first,second,first]}});
assert.equal(calls.length,9);
assert.equal(calls.filter(c=>c.root===first).length,3);
assert.equal(calls.filter(c=>c.root===second).length,3);
handlers.get('filter-content-updated')({target:first});
assert.equal(calls.length,12);
runtime.register({id:'error',run:()=>{throw Error('Synthetic synchronous error');}});
assert.equal(errors.length,1);
runtime.register({id:'after-error',run:context=>calls.push({id:'after-error',...context})});
assert.equal(calls.length,13);
handlers.get('core_filters/contentUpdated')({target:first,detail:{nodes:[]}});
assert.equal(calls.length,17);
assert.equal(errors.length,2);
assert.equal(calls.at(-1).id,'after-error');
assert.equal(Object.hasOwn(window,'localScriptManagerQueue'),false);
assert.equal(Object.hasOwn(window,'localScriptManagerRuntime'),false);
console.log('Actual AMD registration before/after init, context, unique roots, legacy events and error isolation passed.');
