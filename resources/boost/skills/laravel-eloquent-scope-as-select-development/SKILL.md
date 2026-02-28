---
name: laravel-eloquent-scope-as-select-development
description: Build and work with protonemedia/laravel-eloquent-scope-as-select features including reusing Eloquent scopes as boolean select subqueries, registering the macro, and querying per-row scope flags.
license: MIT
metadata:
  author: ProtoneMedia
---

# Eloquent Scope As Select Development

## Overview
Use protonemedia/laravel-eloquent-scope-as-select to reuse Eloquent scopes as boolean select subqueries. Avoids duplicating scope logic when you need per-row flags indicating whether each model matches a given scope.

## When to Activate
- Activate when adding scope-based boolean subqueries to Eloquent queries.
- Activate when code references `ScopeAsSelect`, the `addScopeAsSelect` macro, or scope-as-select patterns.
- Activate when the user wants to register, configure, or use scope-as-select in a Laravel application.

## Scope
- In scope: macro registration, `addScopeAsSelect` usage with closures/strings/arrays, dynamic scopes, flipping results, chaining multiple selects.
- Out of scope: modifying this package's internal source code unless the user explicitly says they are contributing to the package.

## Workflow
1. Identify the task (macro registration, adding scope selects, dynamic scopes, etc.).
2. Read `references/laravel-eloquent-scope-as-select-guide.md` and focus on the relevant section.
3. Apply the patterns from the reference, keeping code minimal and Laravel-native.

## Core Concepts

### Macro Registration
Register the macro in a service provider before using `addScopeAsSelect`:

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

public function boot(): void
{
    ScopeAsSelect::addMacro();
}
```

### Adding a Scope as Select
```php
Post::addScopeAsSelect('is_published', fn ($q) => $q->published())->get();
Post::addScopeAsSelect('is_published', 'published')->get();
Post::addScopeAsSelect('is_popular_and_published', ['popular', 'published'])->get();
```

### Dynamic Scopes
```php
Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->get();
```

### Flipping the Result
```php
Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->get();
```

## Do and Don't

Do:
- Always register the macro via `ScopeAsSelect::addMacro()` in a service provider before using `addScopeAsSelect`.
- Use scopes that are pure query constraints (no side-effects or column modifications).
- Chain multiple `addScopeAsSelect` calls when you need several boolean flags.

Don't:
- Don't forget to register the macro — you'll get `Call to undefined method ... addScopeAsSelect()`.
- Don't invent undocumented methods/options; stick to the docs and reference.
- Don't suggest changing package internals unless the user explicitly wants to contribute upstream.

## References
- `references/laravel-eloquent-scope-as-select-guide.md`
