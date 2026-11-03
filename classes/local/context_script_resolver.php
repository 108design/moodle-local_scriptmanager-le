<?php
/**
 * File documentation for classes/local/context_script_resolver.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   108design source-available software license; see LICENSE.md
 */
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
namespace local_scriptmanager\local;

use local_scriptmanager\local\persistence\script_repository;
use moodle_page;

/** Resolves published managed scripts for safe non-Frontpage Moodle page contexts. */
final class context_script_resolver {
    public function __construct(private readonly script_repository $repository) {
    }

    public function resolve(moodle_page $page): array {
        if (!authority::active() || !$this->is_safe_page($page)) {
            return [];
        }
        return $this->repository->get_context_candidates();
    }

    public function context_data(moodle_page $page): array {
        return [
            'pageId' => 0,
            'pageType' => (string) $page->pagetype,
            'courseId' => isset($page->course->id) ? (int) $page->course->id : 0,
            'categoryId' => isset($page->course->category) ? (int) $page->course->category : 0,
        ];
    }

    private function is_safe_page(moodle_page $page): bool {
        if (during_initial_install() || !get_config('local_scriptmanager', 'version')) {
            return false;
        }
        $pagetype = (string) $page->pagetype;
        foreach (['admin-', 'login-', 'auth-', 'tool-', 'local-scriptmanager-', 'local-frontpage-', 'mod-frontpage-'] as $prefix) {
            if (str_starts_with($pagetype, $prefix)) {
                return false;
            }
        }
        $pagelayout = method_exists($page, 'get_pagelayout') ? $page->get_pagelayout() : $page->pagelayout;
        return !in_array((string) $pagelayout, ['embedded', 'popup', 'maintenance', 'print', 'redirect'], true);
    }
}
