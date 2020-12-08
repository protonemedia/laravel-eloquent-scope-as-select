<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ScopeAsSelect
{
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
    public static function addMacro(string $name = 'addScopeAsSelect')
    {
        Builder::macro($name, function (string $name, $withQuery, bool $exists = true): Builder {
            $callable = is_callable($withQuery)
                ? $withQuery
                : ScopeAsSelect::makeCallable($withQuery);

            // We do this to make sure the $query variable is an Eloquent Query Builder.
            $query = ScopeAsSelect::builder($this);

            $originalTable = $query->getModel()->getTable();

            // Instantiate a new model that uses the aliased table.
            $aliasedTable = "{$name}_{$originalTable}";
            $aliasedModel = $query->newModelInstance()->setTable($aliasedTable);

            // Query the model and explicitly set the targetted table, as the model's table
            // is just the aliased table with the 'as' statement.
            $subSelect = $aliasedModel::query();
            $subSelect->getQuery()->from($originalTable, $aliasedTable);

            // Apply the where constraint based on the model's key name and apply the $callable.
            $subSelect
                ->select(DB::raw(1))
                ->whereColumn($aliasedModel->getQualifiedKeyName(), $query->getModel()->getQualifiedKeyName())
                ->limit(1)
                ->tap(fn ($query) => $callable($query));

            // Add the subquery and query-time cast.
            return $query
                ->addSelect([$name => $subSelect])
                ->withCasts([$name => $exists ? NullableBooleanCaster::class : NegativeNullableBooleanCaster::class]);
        });
    }
}
