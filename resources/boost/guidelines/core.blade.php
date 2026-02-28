{{-- Laravel Eloquent Scope As Select Guidelines for AI Code Assistants --}}
{{-- Source: https://github.com/protonemedia/laravel-eloquent-scope-as-select --}}
{{-- License: MIT | (c) ProtoneMedia --}}

## Eloquent Scope As Select

- `protonemedia/laravel-eloquent-scope-as-select` reuses Eloquent scopes as boolean select subqueries, avoiding duplication of scope logic when you need per-row flags.
- Always activate the `laravel-eloquent-scope-as-select-development` skill when working with scope-as-select subqueries, or any code that uses the `ScopeAsSelect` class or `addScopeAsSelect` macro.
