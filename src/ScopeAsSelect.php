<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Database\Eloquent\Builder;
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

    public static function addMacro(string $name = 'addScopeAsSelect')
    {
        Builder::macro($name, function (string $name, callable $callable): Builder {
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
                ->tap(function ($query) use ($callable) {
                    return $callable($query);
                });

            return $query->withCasts([
                $name => NullableBooleanCaster::class,
            ])->addSelect([$name => $subSelect]);
        });
    }
}
