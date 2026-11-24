---
name: scope-as-select-development
description: Build and work with protonemedia/laravel-eloquent-scope-as-select features including re-using Eloquent query scopes as subquery selects, adding boolean attributes to models, and using closure, string, and array shorthand notations.
license: MIT
metadata:
  author: Protone Media
---

# Scope as Select Development

## Overview
Use protonemedia/laravel-eloquent-scope-as-select to re-use Eloquent query scopes and constraints as subquery selects. Instead of duplicating scope logic in PHP, add scopes as database subqueries that return boolean attributes on every model.

## When to Activate
- Activate when working with `addScopeAsSelect` calls or the `ScopeAsSelect` class in Laravel.
- Activate when the user wants to convert query scopes into boolean model attributes via subqueries.
- Activate when code references `NullableBooleanCaster`, `NegativeNullableBooleanCaster`, or `ScopeAsSelect::addMacro`.

## Scope
- In scope: registering the macro, adding scope-based subquery selects, using closure/string/array notations, flipping results, chaining multiple selects.
- Out of scope: defining the scopes themselves, general Eloquent query building unrelated to subquery selects.

## Workflow
1. Identify the task (macro registration, adding a scope as select, combining multiple scopes, flipping results, etc.).
2. Read `references/scope-as-select-guide.md` and focus on the relevant section.
3. Apply the patterns from the reference, keeping code minimal and Laravel-native.

## Core Concepts

### Macro Registration
Register the macro in a service provider before using `addScopeAsSelect`:

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

public function boot()
{
    ScopeAsSelect::addMacro();
}
```

### Using a Closure
```php
Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();
```

### Using a String Shorthand
```php
Post::addScopeAsSelect('is_published', 'published')->get();
```

### Using an Array for Multiple Scopes
```php
Post::addScopeAsSelect('meets_criteria', ['published', 'popular'])->get();
```

### Using an Associative Array for Dynamic Scopes
```php
Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->get();
```

### Flipping the Result
```php
Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->get();
```

## Do and Don't

Do:
- Always call `ScopeAsSelect::addMacro()` in a service provider before using `addScopeAsSelect`.
- Use string or array shorthand instead of closures when the scope call is straightforward.
- Chain multiple `addScopeAsSelect` calls to add several boolean attributes at once.
- Use the third parameter `false` to flip/negate the boolean result.
- Access the resulting boolean via the attribute name you passed as the first argument (e.g., `$post->is_published`).

Don't:
- Don't reference scope names that don't exist on the model — the query will fail.
- Don't forget to call `->get()` or another terminal method after `addScopeAsSelect` — it returns a query builder.
- Don't use `addScopeAsSelect` without first registering the macro via `ScopeAsSelect::addMacro()`.
- Don't pass a closure as the second argument when a simple string or array shorthand would suffice.

## References
- `references/scope-as-select-guide.md`
