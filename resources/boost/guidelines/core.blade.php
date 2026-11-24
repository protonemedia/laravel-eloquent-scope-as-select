{{-- Laravel Eloquent Scope as Select Guidelines for AI Code Assistants --}}
{{-- Source: https://github.com/protonemedia/laravel-eloquent-scope-as-select --}}
{{-- License: MIT | (c) Protone Media --}}

## Eloquent Scope as Select

- `protonemedia/laravel-eloquent-scope-as-select` lets you re-use Eloquent query scopes and constraints as subquery selects, adding boolean attributes to models without duplicating scope logic in PHP.
- Always activate the `scope-as-select-development` skill when working with `addScopeAsSelect`, `ScopeAsSelect::addMacro`, `NullableBooleanCaster`, or any code that converts query scopes into subquery select statements.
