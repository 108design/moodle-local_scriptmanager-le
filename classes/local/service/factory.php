<?php
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
namespace local_scriptmanager\local\service;
use local_scriptmanager\local\persistence\script_repository;
use local_scriptmanager\local\module_authority;
/** Ownership is independent of external services. */
final class factory {
    public static function script_repository(): script_repository {
        global $DB;
        module_authority::assert_owner('scriptmanager');
        return new script_repository($DB);
    }
    public static function read_script_repository(): script_repository { global $DB; return new script_repository($DB); }
    public static function runtime_script_repository(): script_repository { return self::script_repository(); }
}
