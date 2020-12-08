<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WhereNot
{
    /**
     * The count for each table.
     *
     * @var array
     */
    protected static $tableSubCount = [];

    /**
     * Makes an alias for the given table.
     *
     * @param string $table
     * @return string
     */
    public static function getTableAlias($table): string
    {
        if (!array_key_exists($table, static::$tableSubCount)) {
            static::$tableSubCount[$table] = 0;
        }

        $count = static::$tableSubCount[$table]++;

        return "where_not_{$count}_{$table}";
    }

    /**
     * Helper method for code completion.
     *
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function builder($value): Builder
    {
        return $value;
    }

    /**
     * Returns a callable that applies the scope and arguments
     * to the given query builder.
     *
     * @param mixed $value
     * @return callable
     */
    public static function makeCallable($value): callable
    {
        // We both allow single and multiple scopes...
        $scopes = Arr::wrap($value);

        return function ($query) use ($scopes) {
            // If $scope is numeric, there are no arguments, and we can
            // safely assume the scope is in the $arguments variable.
            foreach ($scopes as $scope => $arguments) {
                if (is_numeric($scope)) {
                    [$scope, $arguments] = [$arguments, null];
                }

                // As we allow a constraint to be a single arguments.
                $arguments = Arr::wrap($arguments);

                $query->{$scope}(...$arguments);
            }

            return $query;
        };
    }

    /**
     * Adds a macro to the query builder.
     *
     * @param string $name
     * @return void
     */
    public static function addMacro(string $name = 'whereNot')
    {
        Builder::macro($name, function ($withQuery): Builder {
            $callable = is_callable($withQuery)
                ? $withQuery
                : WhereNot::makeCallable($withQuery);

            // We do this to make sure the $query variable is an Eloquent Query Builder.
            $builder = WhereNot::builder($this);

            return $builder->whereNotExists(function ($query) use ($callable, $builder) {
                // Create a new Eloquent Query Builder with the given Query Builder and
                // set the model from the original builder.
                $query = new Builder($query);
                $query->setModel($builder->getModel());

                $originalTable = $query->getModel()->getTable();

                // Instantiate a new model that uses the aliased table.
                $aliasedTable = WhereNot::getTableAlias($originalTable);
                $aliasedModel = $query->newModelInstance()->setTable($aliasedTable);

                // Apply the where constraint based on the model's key name and apply the $callable.
                $query
                    ->select(DB::raw(1))
                    ->from($originalTable, $aliasedTable)
                    ->whereColumn($aliasedModel->getQualifiedKeyName(), 'posts.id')
                    ->limit(1)
                    ->tap(fn ($query) => $callable($query));
            });
        });
    }
}
