<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Construct the component repositories and application services.
 *
 * @package local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */

// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager\local\service;

use local_scriptmanager\local\persistence\script_repository;
use local_scriptmanager\local\module_authority;

/**
 * Ownership is independent of external services.
 */
final class factory {
    /**
     * Construct the repository used to author managed scripts.
     *
     * @return script_repository
     */
    public static function script_repository(): script_repository {
        global $DB;
        module_authority::assert_owner('scriptmanager');
        return new script_repository($DB);
    }
    /**
     * Construct the repository used by read-only script views.
     *
     * @return script_repository
     */
    public static function read_script_repository(): script_repository {
        global $DB;
        return new script_repository($DB);
    }
    /**
     * Construct the repository used to select published runtime scripts.
     *
     * @return script_repository
     */
    public static function runtime_script_repository(): script_repository {
        return self::script_repository();
    }
}
