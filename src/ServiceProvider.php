<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the shared binding.
     */
    public function register()
    {
        ScopeAsSelect::addMacro('addScopeAsSelect');
    }
}
