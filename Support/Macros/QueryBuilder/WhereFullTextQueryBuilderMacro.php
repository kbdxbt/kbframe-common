<?php

declare(strict_types=1);

namespace Modules\Common\Support\Macros\QueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
class WhereFullTextQueryBuilderMacro
{
    public function whereFullText(): callable
    {
        /*
         * Add a "where fulltext" clause to the query.
         *
         * @param  string|string[]  $columns
         * @param  string  $value
         * @param  string  $boolean
         * @return $this
         */
        return function ($columns, $value, array $options = [], $boolean = 'and') {
            $type = 'Fulltext';

            $columns = (array) $columns;

            $this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

            $this->addBinding($value);

            return $this;
        };
    }

    public function orWhereFullText(): callable
    {
        /*
         * Add a "or where fulltext" clause to the query.
         *
         * @param  string|string[]  $columns
         * @param  string  $value
         * @return $this|callable
         */
        return fn ($columns, $value, array $options = []) => $this->whereFulltext($columns, $value, $options, 'or');
    }
}
