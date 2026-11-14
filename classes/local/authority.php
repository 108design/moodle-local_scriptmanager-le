<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Runtime ownership survives licence expiry; it never follows saved content.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */

namespace local_scriptmanager\local;

/**
 * Runtime ownership survives licence expiry; it never follows saved content.
 */
final class authority {
    /**
     * Report whether this component currently owns its module.
     *
     * @return bool
     */
    public static function active(): bool {
        return !during_initial_install() && (bool)get_config('local_scriptmanager', 'version')
            && module_authority::is_owner('scriptmanager');
    }
}
