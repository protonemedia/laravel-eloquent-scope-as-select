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

    public static function makeCallable($value): callable
    {
        $scopes = Arr::wrap($value);

        return function ($query) use ($scopes) {
            foreach ($scopes as $key => $scope) {
                $arguments = [];

                if (is_string($key)) {
                    $arguments = Arr::wrap($scope);

                    $scope = $key;
                }

                $query->{$scope}(...$arguments);
            }

            return $query;
        };
    }

    public static function addMacro(string $name = 'addScopeAsSelect')
    {
        Builder::macro($name, function (string $name, $withQuery): Builder {
            $callable = is_callable($withQuery)
                ? $withQuery
                : ScopeAsSelect::makeCallable($withQuery);

            $query = ScopeAsSelect::builder($this);

            $originalTable = $query->getModel()->getTable();

            $aliasedTable = "{$name}_{$originalTable}";
            $aliasedModel = $query->newModelInstance()->setTable($aliasedTable);

            $subSelect = $aliasedModel::query()->setModel($aliasedModel);
            $subSelect->getQuery()->from("{$originalTable} as {$aliasedTable}");

            $subSelect
                ->select(DB::raw(1))
                ->whereColumn($aliasedModel->getQualifiedKeyName(), $query->getModel()->getQualifiedKeyName())
                ->limit(1)
                ->tap(fn ($query) => $callable($query));

            return $query
                ->addSelect([$name => $subSelect])
                ->withCasts([$name => NullableBooleanCaster::class]);
        });
    }
}
