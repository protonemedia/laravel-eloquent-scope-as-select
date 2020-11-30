<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the shared binding.
     */
    public function register()
    {
        Builder::macro('addScopeAsSelect', function ($name, callable $callable) {
            $query = $this;

            $originalModel = $query->getModel();

            $table = $originalModel->getTable();

            $as = "{$name}_{$table}";

            $asTable = "{$table} as {$as}";

            $subSelect = $originalModel::query();

            $model = $originalModel->newModelInstance() ?: (clone $subSelect->getModel());
            $model->setTable($as);

            $subSelect->setModel($model);
            $subSelect->getQuery()->from($asTable);

            $subSelect
                ->select(DB::raw(1))
                ->whereColumn($model->getQualifiedKeyName(), $originalModel->getQualifiedKeyName())
                ->tap(function ($query) use ($callable) {
                    $callable($query);
                });

            $query->withCasts([
                $name => NullableBooleanCaster::class,
            ])->addSelect([$name => $subSelect]);

            return $this;
        });
    }
}
