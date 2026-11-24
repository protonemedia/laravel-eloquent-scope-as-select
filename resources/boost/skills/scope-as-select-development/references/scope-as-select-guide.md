# Laravel Eloquent Scope as Select Reference

Complete reference for `protonemedia/laravel-eloquent-scope-as-select`. Full documentation: https://github.com/protonemedia/laravel-eloquent-scope-as-select

## Installation

```bash
composer require protonemedia/laravel-eloquent-scope-as-select
```

## Macro Registration

Register the macro in your `AppServiceProvider` (or any service provider):

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

public function boot()
{
    ScopeAsSelect::addMacro();
}
```

### Custom Macro Name

You can customize the macro name:

```php
ScopeAsSelect::addMacro('withScopeAsSubQuery');

// Then use:
Post::withScopeAsSubQuery('is_published', 'published')->get();
```

## Method Signature

```php
addScopeAsSelect(string $name, callable|string|array $withQuery, bool $exists = true): Builder
```

- `$name` — the attribute name added to each model (e.g., `'is_published'`).
- `$withQuery` — the scope(s) to apply. Accepts a closure, a scope name string, or an array of scopes.
- `$exists` — when `true` (default), the attribute is `true` if the scope matches. When `false`, the result is flipped.

## Adding Scope as Select

### Using a Closure

Full control over the subquery with a closure:

```php
$posts = Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();

$posts->each(function (Post $post) {
    $post->is_published; // true or false
});
```

With a short closure:

```php
$posts = Post::addScopeAsSelect('is_published', fn ($query) => $query->published())->get();
```

### Using a String

Pass the scope name directly as a string:

```php
Post::addScopeAsSelect('is_published', 'published')->get();
```

This is equivalent to:

```php
Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();
```

### Using an Array for Multiple Scopes

Combine multiple scopes — all must match for the attribute to be `true`:

```php
Post::addScopeAsSelect('is_popular_and_published', ['popular', 'published'])->get();
```

### Using an Associative Array for Dynamic Scopes

Pass arguments to dynamic scopes:

```php
Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->get();
```

### Dynamic Scopes with Multiple Arguments

When a dynamic scope requires multiple arguments, use an array:

```php
Post::addScopeAsSelect('published_between', ['publishedBetween' => [2010, 2020]])->get();
```

### Mixing Static and Dynamic Scopes

Combine scopes with and without arguments in a single array:

```php
Post::addScopeAsSelect('is_published_announcement', [
    'published',
    'ofType' => 'announcement',
])->get();
```

## Flipping the Result

Set the third parameter to `false` to negate the boolean:

```php
$post = Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->first();

$post->is_not_announcement; // true if the post is NOT an announcement
```

## Chaining Multiple Selects

Add multiple boolean attributes by chaining calls:

```php
Post::query()
    ->addScopeAsSelect('is_published', 'published')
    ->addScopeAsSelect('is_recent_and_popular', function ($query) {
        $query->publishedInCurrentYear()->has('comments', '>=', 10);
    })
    ->get()
    ->each(function (Post $post) {
        $post->is_published;          // boolean
        $post->is_recent_and_popular; // boolean
    });
```

## Using with Inline Constraints

You can combine `addScopeAsSelect` with other query constraints:

```php
Post::query()
    ->addScopeAsSelect('has_comments', fn ($query) => $query->has('comments'))
    ->where('published_at', '>=', now()->subMonth())
    ->get();
```

## How It Works

Under the hood, the package:

1. Creates an aliased table reference for the model.
2. Builds a subquery that `SELECT 1` where the primary keys match and the scope conditions are met (with `LIMIT 1`).
3. Adds the subquery as a select on the main query.
4. Casts the result using `NullableBooleanCaster` (NULL becomes `false`, 1 becomes `true`) or `NegativeNullableBooleanCaster` when the result is flipped (NULL becomes `true`, 1 becomes `false`).

## Boolean Casters

The package includes two query-time casters:

- `NullableBooleanCaster` — casts `NULL` to `false` and `1` to `true`. Used by default.
- `NegativeNullableBooleanCaster` — casts `NULL` to `true` and `1` to `false`. Used when the third parameter is `false`.

These casters are applied automatically and require no manual configuration.

## Practical Example

A `Post` model with scopes:

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopePublishedInCurrentYear($query)
    {
        return $query->whereYear('published_at', date('Y'));
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
```

Fetching all posts with boolean attributes:

```php
$posts = Post::query()
    ->addScopeAsSelect('is_published', 'published')
    ->addScopeAsSelect('is_current_year', 'publishedInCurrentYear')
    ->addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])
    ->addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)
    ->get();

$posts->each(function (Post $post) {
    $post->is_published;        // bool
    $post->is_current_year;     // bool
    $post->is_announcement;     // bool
    $post->is_not_announcement; // bool (inverse of is_announcement)
});
```
