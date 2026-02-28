# Laravel Eloquent Scope As Select Reference

Complete reference for `protonemedia/laravel-eloquent-scope-as-select`. Full documentation: https://github.com/protonemedia/laravel-eloquent-scope-as-select#readme

## Purpose

Avoid duplicating scope/constraint logic in PHP by reusing Eloquent scopes as **boolean select subqueries**.

Example goal:

- Fetch all posts.
- Still know whether each post matches a complex scope like `publishedInCurrentYear()->has('comments', '>=', 10)`.

## Installation

```bash
composer require protonemedia/laravel-eloquent-scope-as-select
```

## Registering the macro

You must add the macro to the query builder (e.g. in `AppServiceProvider::boot()`).

Default macro name: `addScopeAsSelect`.

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

public function boot(): void
{
    ScopeAsSelect::addMacro();

    // Or register with a custom macro name:
    // ScopeAsSelect::addMacro('withScopeAsSubQuery');
}
```

Pitfall: if you forget this, you’ll get `Call to undefined method ... addScopeAsSelect()`.

## Core API: addScopeAsSelect

Signature conceptually:

- `addScopeAsSelect(string $alias, Closure|string|array $scopeSpec, bool $expected = true)`

Where:

- `$alias` becomes a selected attribute on each model.
- `$scopeSpec` describes the scope(s) to apply to the subquery.
- `$expected` (3rd argument) can flip the result.

## Usage examples

### Using a closure

```php
$posts = Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();

$posts->first()->is_published; // boolean-ish
```

Short closure:

```php
Post::addScopeAsSelect('is_published', fn ($query) => $query->published())->get();
```

### Using a string (scope name)

```php
Post::addScopeAsSelect('is_published', 'published')->get();
```

### Multiple scopes (array)

```php
Post::addScopeAsSelect('is_popular_and_published', ['popular', 'published'])->get();
```

### Dynamic scopes (associative array)

```php
Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->get();
```

Multiple arguments to dynamic scopes:

```php
Post::addScopeAsSelect('is_announcement', ['publishedBetween' => [2010, 2020]])->get();
```

Mix dynamic + non-dynamic:

```php
Post::addScopeAsSelect('is_published_announcement', [
    'published',
    'ofType' => 'announcement',
])->get();
```

### Flipping the result

Optional third argument flips the “expected” value (README example uses `false`):

```php
$postA = Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->first();
$postB = Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->first();

$postA->is_announcement;      // true
$postB->is_not_announcement;  // false
```

## Multiple selects

You can chain multiple calls to add more computed flags:

```php
Post::query()
    ->addScopeAsSelect('is_published', fn ($q) => $q->published())
    ->addScopeAsSelect('is_recent_and_popular', function ($q) {
        $q->publishedInCurrentYear()->has('comments', '>=', 10);
    })
    ->get();
```

## Common patterns

- Add computed boolean flags for UI filters without having to re-run expensive scope logic per model.
- Keep the scope definitions as the single source of truth.

## Pitfalls / gotchas

- **Macro registration is required** (see above).
- **SQL performance:** scope-as-select adds subqueries; watch for N+1-like query cost in SQL.
- **Scope side-effects:** scopes should be “pure” query constraints; avoid scopes that select/modify columns in surprising ways.
