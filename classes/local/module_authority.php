<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/module_authority.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
namespace local_scriptmanager\local;

defined('MOODLE_INTERNAL') || die();

/** Persistent ownership for the script configuration. */
final class module_authority {
    private const HOST = 'local_scriptmanager';
    private static array $writing = [];

    private static function component(string $module): string {
        if (!in_array($module, ['scriptmanager'], true)) {
            throw new \coding_exception('Unknown script module.');
        }
        return 'local_' . $module;
    }

    /** Reading ownership never claims it from a menu/script or changes licence state. */
    public static function owner(string $module): string {
        $component = self::component($module);
        if (get_config($component, 'frontpagemigrationcompleted')) {
            return 'local_frontpage';
        }
        $saved = get_config($component, 'moduleowner');
        if ($saved !== false && $saved !== '') {
            return in_array($saved, ['local_frontpage', $component], true) ? $saved : 'conflict';
        }
        $feature = $module . '.pro';
        $hub = '\\local_lmh108\\local\\license_state';
        if (get_config('local_lmh108', 'version') && class_exists($hub) && ($record = $hub::record($feature))) {
            $source = (string) ($record->sourcecomponent ?? '');
            if ($source === $component) {
                return $component;
            }
            if (in_array($source, ['local_frontpage', 'block_frontpage_menu', 'filter_frontpage_menu'], true)) {
                return 'local_frontpage';
            }
        }
        // Configured activation records retain their host even after licence expiry.
        $sourceconfigured = (string) get_config($component, 'activationid') !== '';
        $targetconfigured = false;
        $target = '\\local_frontpage\\local\\licensing\\standalone_state';
        if (get_config('local_frontpage', 'version') && class_exists($target)) {
            $targetconfigured = $target::record($feature) !== null;
        }
        if ($sourceconfigured && $targetconfigured) {
            return 'conflict';
        }
        if ($targetconfigured) {
            return 'local_frontpage';
        }
        if ($sourceconfigured || get_config($component, 'version')) {
            return $component;
        }
        return $component;
    }

    /** Called by installation/upgrade; an existing decision is never replaced. */
    public static function initialise(string $module): void {
        $component = self::component($module);
        $lock = self::lock($module);
        try {
            if (!get_config($component, 'moduleowner')) {
                $owner = self::owner($module);
                set_config('moduleowner', $owner, $component);
            }
        } finally {
            $lock->release();
        }
    }

    public static function is_owner(string $module): bool {
        return self::owner($module) === self::HOST;
    }

    public static function assert_owner(string $module): void {
        if (!self::is_owner($module)) {
            throw new \moodle_exception('moduleinactive', self::HOST, self::owner_url($module),
                self::owner_label($module));
        }
    }

    /** Keep ownership and each normal write serialized with the explicit handover. */
    public static function write(string $module, callable $operation): mixed {
        if (!empty(self::$writing[$module])) {
            self::assert_owner($module);
            return $operation();
        }
        $lock = self::lock($module);
        try {
            self::assert_owner($module);
            \require_capability('local/scriptmanager:manage', \context_system::instance());
            self::$writing[$module] = true;
            return $operation();
        } finally {
            unset(self::$writing[$module]);
            $lock->release();
        }
    }

    private static function lock(string $module): \core\lock\lock {
        $lock = \core\lock\lock_config::get_lock_factory('local_frontpage_modules')->get_lock($module, 10);
        if (!$lock) {
            throw new \moodle_exception('locktimeout');
        }
        return $lock;
    }

    private static function owner_label(string $module): string {
        return self::owner($module) === 'local_frontpage' ? 'Frontpage'
            : get_string('pluginname', self::component($module));
    }

    public static function owner_url(string $module): \moodle_url {
        $owner = self::owner($module);
        if ($owner === 'conflict') {
            return new \moodle_url('/admin/search.php');
        }
        return new \moodle_url('/local/' . ($owner === 'local_frontpage' ? 'frontpage' : $module)
            . '/scripts.php');
    }

    /** Inactive tabs remain reachable and explain their owner instead of exposing another editor. */
    public static function notice(string $module): string {
        global $OUTPUT;
        $owner = self::owner($module);
        if ($owner === 'conflict') {
            return $OUTPUT->notification(get_string('moduleownershipconflict', self::HOST), 'warning');
        }
        $html = $OUTPUT->notification(get_string('moduleinactive', self::HOST, self::owner_label($module)), 'info');
        $html .= \html_writer::link(self::owner_url($module),
            get_string('moduleopenowner', self::HOST, self::owner_label($module)), ['class' => 'btn btn-secondary']);
        return $html;
    }

}
