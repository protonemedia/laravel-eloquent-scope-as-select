<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class NullableBooleanCaster implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return (bool) $value;
    }

    public function set($model, $key, $value, $attributes)
    {
    }
}
