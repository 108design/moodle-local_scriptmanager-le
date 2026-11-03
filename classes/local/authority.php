<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/authority.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
namespace local_scriptmanager\local;

/** Runtime ownership survives licence expiry; it never follows saved content. */
final class authority {
    public static function active(): bool {
        return !during_initial_install() && (bool)get_config('local_scriptmanager', 'version')
            && module_authority::is_owner('scriptmanager');
    }
}
