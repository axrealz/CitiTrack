<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Shared "search as you type" matching used by the list pages.
 *
 *  - Case-insensitive (follows the column collation, utf8mb4_unicode_ci by default).
 *  - A row matches if the text appears in ANY of the given columns.
 *  - Rows whose FIRST column starts with the text come first, then rows whose
 *    second column starts with it, then everything else (the page's own
 *    ordering, e.g. newest first, is applied after that).
 *  - % and _ typed by the user are matched literally.
 *  - Everything is bound as parameters, so it is safe from SQL injection.
 */
class Search
{
    /**
     * @param EloquentBuilder|QueryBuilder $query
     * @param string[] $columns  hard-coded column names, most important first
     */
    public static function apply($query, ?string $term, array $columns): void
    {
        $term = trim((string) $term);

        if ($term === '' || $columns === []) {
            return;
        }

        $escaped = addcslashes($term, '\\%_');

        // (col1 LIKE %x% OR col2 LIKE %x% ...) grouped, so it never escapes
        // other conditions such as "only this cashier's sales" or soft deletes.
        $query->where(function ($q) use ($columns, $escaped) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$escaped}%");
            }
        });

        // Prefix matches first
        $cases = [];
        $bindings = [];

        foreach (array_values($columns) as $i => $column) {
            $cases[]    = "WHEN `{$column}` LIKE ? THEN {$i}";
            $bindings[] = "{$escaped}%";
        }

        $query->orderByRaw('CASE ' . implode(' ', $cases) . ' ELSE ' . count($columns) . ' END', $bindings);
    }
}