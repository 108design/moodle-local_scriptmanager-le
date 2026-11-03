# Script Manager

Manage named JavaScript snippets for trusted Moodle administrators. Moodle 4.5 or later is required.

Save drafts, publish immutable versions, compare revisions and restore an earlier version into the editor. Restoring requires a deliberate publication before the restored code becomes live. Enable or disable each script separately. New snippets start disabled and unpublished.

Active published scripts run at the existing footer hook on permitted normal Moodle pages. Administration, login, authentication, tool, manager and Frontpage page types, installation and special layouts remain excluded. The manager and login routes support recovery.

The independent CodeMirror editor provides JavaScript editing. The runtime supplies context.root, pageType, pageId, courseId and categoryId, and reinitialises snippets on Moodle content updates. Use repeatable initialisers. Selection follows script names alphabetically and does not wait for asynchronous work.

Scripts run with each visitor’s browser permissions and can access the page and network. Keep secrets out of browser code. Synchronous errors are logged; this does not isolate loops, asynchronous failures or changes already made. Disabling a script affects future delivery and cannot revoke code already executed or cached.

Concurrent editors use the last saved draft; there is no optimistic draft conflict check.

The included functions are Free Features under Section 2 of LICENSE.md and require no external activation. The 108design source-available licence, its Moodle Marketplace permission and third-party CodeMirror notices apply.
