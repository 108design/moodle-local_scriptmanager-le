<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/script_revision_diff.php.
 *
 * @package   local_scriptmanager
 * @copyright 2026 Andreas Giesen
 * @license   108design source-available software license; see LICENSE.md
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_scriptmanager\local;

/** Bounded read-only line comparison. Script source is data, never executed. */
final class script_revision_diff {
    /** Resolve both versions only from this script's authorised history. */
    public static function select(array $versions, int $scriptid, int $from = 0, int $to = 0): ?array {
        $own = [];
        foreach ($versions as $v) {
            if ((int)$v->scriptid === $scriptid) { $own[(int)$v->id] = $v; }
        }
        uasort($own, static fn($a, $b) => (int)$b->versionnumber <=> (int)$a->versionnumber);
        foreach ([$from, $to] as $id) {
            if ($id && !isset($own[$id])) { throw new \invalid_parameter_exception('Revision does not belong to this script.'); }
        }
        if (count($own) < 2 && !$from && !$to) { return null; }
        $ids = array_keys($own);
        if (!$ids) { throw new \invalid_parameter_exception('No script revision to compare.'); }
        return [$own[$from ?: ($ids[1] ?? $ids[0])], $own[$to ?: $ids[0]]];
    }

    /** Myers edits with a bounded trace; large replacements remain exact as a contiguous block. */
    public static function operations(string $before, string $after, int $budget = 100000): array {
        $split = static fn($s) => $s === '' ? [] : explode("\n", str_replace(["\r\n", "\r"], "\n", $s));
        return self::token_operations($split($before), $split($after), $budget);
    }

    /** Unicode character edits against the preceding revision, including whitespace. */
    public static function characters(string $before, string $after): ?array {
        // Token alignment keeps existing code recognizable in substantial rewrites.
        if (strlen($before) + strlen($after) > 100000) { return null; }
        $pattern = '/\s+|[\p{L}\p{N}_]+|[^\s\p{L}\p{N}_]/u';
        if (preg_match_all($pattern, $before, $a) === false
                || preg_match_all($pattern, $after, $b) === false) { return null; }
        $tokens = self::aligned_tokens($a[0], $b[0]);
        $counts = ['add' => 0, 'remove' => 0]; $removed = ''; $added = '';
        $flush = static function() use (&$removed, &$added, &$counts): void {
            if ($removed === '' && $added === '') { return; }
            $chars = self::token_operations(preg_split('//u', $removed, -1, PREG_SPLIT_NO_EMPTY),
                preg_split('//u', $added, -1, PREG_SPLIT_NO_EMPTY), 100000);
            foreach ($chars['operations'] as [$type, $character]) {
                if ($type !== 'equal') { $counts[$type]++; }
            }
            $removed = ''; $added = '';
        };
        foreach ($tokens['operations'] as [$type, $token]) {
            if ($type === 'equal') { $flush(); }
            else if ($type === 'remove') { $removed .= $token; }
            else { $added .= $token; }
        }
        $flush();
        return $counts;
    }

    /** Word alignment with compact direction rows instead of a large Myers trace. */
    private static function aligned_tokens(array $a, array $b): array {
        $n = count($a); $m = count($b);
        if ($n * $m > 2000000) { return self::token_operations($a, $b, 100000); }
        $previous = array_fill(0, $m + 1, 0); $trace = [];
        $weights = array_map(static fn($token) => preg_match_all('/./us', $token), $a);
        for ($i = 1; $i <= $n; $i++) {
            $current = [0]; $directions = '';
            for ($j = 1; $j <= $m; $j++) {
                $equal = $a[$i - 1] === $b[$j - 1];
                $diagonal = $equal ? $previous[$j - 1] + $weights[$i - 1] : -1;
                if ($diagonal >= max($previous[$j], $current[$j - 1])) {
                    $current[$j] = $diagonal; $directions .= '=';
                } else if ($previous[$j] >= $current[$j - 1]) {
                    $current[$j] = $previous[$j]; $directions .= '^';
                } else { $current[$j] = $current[$j - 1]; $directions .= '<'; }
            }
            $trace[] = $directions; $previous = $current;
        }
        $edits = []; $i = $n; $j = $m;
        while ($i || $j) {
            $direction = $i && $j ? $trace[$i - 1][$j - 1] : ($i ? '^' : '<');
            if ($direction === '=') { $edits[] = ['equal', $a[--$i]]; $j--; }
            else if ($direction === '^') { $edits[] = ['remove', $a[--$i]]; }
            else { $edits[] = ['add', $b[--$j]]; }
        }
        return ['operations' => array_reverse($edits), 'coarse' => false];
    }

    private static function token_operations(array $a, array $b, int $budget): array {
        $prefix = []; $suffix = [];
        $start = 0; $na = count($a); $nb = count($b);
        while ($start < min($na, $nb) && $a[$start] === $b[$start]) {
            $prefix[] = ['equal', $a[$start++]];
        }
        while ($na > $start && $nb > $start && $a[$na - 1] === $b[$nb - 1]) {
            $suffix[] = ['equal', $a[--$na]]; $nb--;
        }
        $a = array_slice($a, $start, $na - $start); $b = array_slice($b, $start, $nb - $start);
        if (!$a || !$b) {
            return ['operations' => array_merge($prefix,
                array_map(static fn($s) => ['remove', $s], $a),
                array_map(static fn($s) => ['add', $s], $b), array_reverse($suffix)), 'coarse' => false];
        }
        $n = count($a); $m = count($b); $v = [1 => 0]; $trace = []; $work = 0; $coarse = false; $edits = [];
        for ($d = 0; $d <= $n + $m; $d++) {
            $trace[] = $v;
            for ($k = -$d; $k <= $d; $k += 2) {
                if (++$work > $budget) { $coarse = true; break 2; }
                $x = ($k === -$d || ($k !== $d && ($v[$k - 1] ?? -1) < ($v[$k + 1] ?? -1)))
                    ? ($v[$k + 1] ?? 0) : ($v[$k - 1] ?? 0) + 1;
                $y = $x - $k;
                while ($x < $n && $y < $m && $a[$x] === $b[$y]) {
                    $x++; $y++;
                    if (++$work > $budget) { $coarse = true; break 3; }
                }
                $v[$k] = $x;
                if ($x >= $n && $y >= $m) {
                    for ($depth = $d; $depth >= 0; $depth--) {
                        $old = $trace[$depth]; $diagonal = $x - $y;
                        $previous = ($diagonal === -$depth || ($diagonal !== $depth
                            && ($old[$diagonal - 1] ?? -1) < ($old[$diagonal + 1] ?? -1)))
                            ? $diagonal + 1 : $diagonal - 1;
                        $px = $old[$previous] ?? 0; $py = $px - $previous;
                        while ($x > $px && $y > $py) { $edits[] = ['equal', $a[--$x]]; $y--; }
                        if ($depth > 0) {
                            if ($x === $px) { $edits[] = ['add', $b[--$y]]; }
                            else { $edits[] = ['remove', $a[--$x]]; }
                        }
                    }
                    $edits = array_reverse($edits); break 2;
                }
            }
        }
        if ($coarse) {
            $edits = array_merge(array_map(static fn($s) => ['remove', $s], $a),
                array_map(static fn($s) => ['add', $s], $b));
        }
        return ['operations' => array_merge($prefix, $edits, array_reverse($suffix)), 'coarse' => $coarse];
    }

    /** Changed line ranges with three unchanged context lines on either side. */
    public static function hunks(array $operations): array {
        $rows = []; $ranges = []; $old = 1; $new = 1;
        foreach ($operations as $i => [$type, $text]) {
            $rows[] = ['type' => $type, 'text' => $text, 'old' => $old, 'new' => $new];
            if ($type !== 'add') { $old++; }
            if ($type !== 'remove') { $new++; }
            if ($type !== 'equal') {
                $start = max(0, $i - 3); $end = min(count($operations) - 1, $i + 3);
                $last = count($ranges) - 1;
                if ($last >= 0 && $start <= $ranges[$last][1] + 1) { $ranges[$last][1] = $end; }
                else { $ranges[] = [$start, $end]; }
            }
        }
        return array_map(static fn($r) => array_slice($rows, $r[0], $r[1] - $r[0] + 1), $ranges);
    }
}
