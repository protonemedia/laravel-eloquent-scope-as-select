# Laravel Eloquent Scope as Select

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-eloquent-scope-as-select)
![run-tests](https://github.com/protonemedia/laravel-eloquent-scope-as-select/workflows/run-tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://scrutinizer-ci.com/g/protonemedia/laravel-eloquent-scope-as-select)
[![Total Downloads](https://img.shields.io/packagist/dt/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-eloquent-scope-as-select)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen)](https://plant.treeware.earth/protonemedia/laravel-eloquent-scope-as-select)

Stop duplicating your Eloquent query scopes and constraints in PHP. This package lets you re-use your query scopes and constraints by adding them as a subquery.

### 📺 Want to see this package in action? Join the live stream on December 3 at 14:00 CET: [https://youtu.be/0vR8IQSFsfQ](https://youtu.be/0vR8IQSFsfQ)

## Requirements

* PHP 7.4+
* Laravel 7.0 and higher

This package has been tested with MySQL 5.7+ but others drivers should work as well.

## Features

* Add a subquery based on a [query scope](https://laravel.com/docs/8.x/eloquent#query-scopes)
* Add a subquery using a Closure
* Support more than one subquery
* Zero third-party dependencies

## Support

We proudly support the community by developing Laravel packages and giving them away for free. Keeping track of issues and pull requests takes time, but we're happy to help! If this package saves you time or if you're relying on it professionally, please consider [supporting the maintenance and development](https://github.com/sponsors/pascalbaljet).

## Blogpost

If you want to know more about the background of this package, please read the blogpost: [Stop duplicating your Eloquent query scopes and constraints. Re-use them as select statements with a new Laravel package](https://protone.media/blog/stop-duplicating-your-eloquent-query-scopes-and-constraints-re-use-them-as-select-statements-with-a-new-laravel-package).

## Installation

You can install the package via composer:

```bash
composer require protonemedia/laravel-eloquent-scope-as-select
```

Add the `macro` to the query builder, for example, in your `AppServiceProvider`. By default, the name of the macro is `addScopeAsSelect`, but you can customize it with the first parameter of the `addMacro` method.

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

public function boot()
{
    ScopeAsSelect::addMacro();

    // custom method:
    ScopeAsSelect::addMacro('withScopeAsSubQuery');
}
```

## Short API description

For a more practical explanation, check out the [usage](#usage) section below.

Add a select using a Closure:
```php
$posts = Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();
```

Using a string, where the second argument is the name of the scope:
```php
$posts = Post::addScopeAsSelect('is_published', 'published')->get();
```

Using an array to call multiple scopes:
```php
$posts = Post::addScopeAsSelect('is_popular_and_published', ['popular', 'published'])->get();
```

Using an associative array to call dynamic scopes:
```php
$posts = Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->get();
```

Using an associative array to call dynamic scopes with multiple arguments:
```php
$posts = Post::addScopeAsSelect('is_announcement', ['publishedBetween' => [2010, 2020]])->get();
```

Using an associative array to mix dynamic and non-dynamic scopes:
```php
$posts = Post::addScopeAsSelect('is_published_announcement', [
    'published',
    'ofType' => 'announcement'
])->get();
```

The method supports an optional third argument that flips the result.

```php
$posts = Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->get();
```

## Usage

Imagine you have a `Post` Eloquent model with a query scope.

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
```

Now you can fetch all published posts by calling the scope method on the query:

```php
$allPublishedPosts = Post::published()->get();
```

But what if you want to fetch *all* posts and *then* check if the post is published? This scope is quite simple, so you can easily mimic the scope's outcome by checking the `published_at` attribute:

```php
Post::get()->each(function (Post $post) {
    $isPublished = !is_null($post->published_at);
});
```

This is harder to achieve when scopes get more complicated or when you chain various scopes. Let's add a relationship and another scope to the `Post` model:

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
}
```

Using Eloquent, we can fetch all posts from this year with at least ten comments.

```php
$recentPopularPosts = Post::query()
    ->publishedInCurrentYear()
    ->has('comments', '>=', 10)
    ->get();
```

Great! Now we want to fetch all posts again, and then check if the post was published this year and has at least ten comments.

```php
Post::get()->each(function (Post $post) {
    $isRecentAndPopular = $post->comments()->count() >= 10
        && optional($post->published_at)->isCurrentYear();
});
```

Well, you get the idea. This is bound to get messy and you're duplicating logic as well.

### Solution

Using the power of this package, you can re-use your scopes when fetching data. The first example (`published` scope) can be narrowed down to:

```php
$posts = Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
})->get();
```

With short closures, a feature which was introduced in PHP 7.4, this can be even shorter:

```php
$posts = Post::addScopeAsSelect('is_published', fn ($query) => $query->published())->get();
```

Now every `Post` model will have an `is_published` boolean attribute.

```php
$posts->each(function (Post $post) {
    $isPublished = $post->is_published;
});
```

You can add multiple selects as well, for example, to combine both scenarios:

```php
Post::query()
    ->addScopeAsSelect('is_published', function ($query) {
        $query->published();
    })
    ->addScopeAsSelect('is_recent_and_popular', function ($query) {
        $query->publishedInCurrentYear()->has('comments', '>=', 10);
    })
    ->get()
    ->each(function (Post $post) {
        $isPublished = $post->is_published;

        $isRecentAndPopular = $post->is_recent_and_popular;
    });
```

### Shortcuts

Instead of using a Closure, there are some shortcuts you could use (see also: [Short API description](#short-api-description)):

Using a string instead of a Closure:

```php
Post::addScopeAsSelect('is_published', function ($query) {
    $query->published();
});

// is the same as:

Post::addScopeAsSelect('is_published', 'published');
```

Using an array instead of Closure, to support multiple scopes and dynamic scopes:

```php
Post::addScopeAsSelect('is_announcement', function ($query) {
    $query->ofType('announcement');
});

// is the same as:

Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement']);
```

You can also flip the result with the optional third parameters (it defaults to `true`):

```php
$postA = Post::addScopeAsSelect('is_announcement', ['ofType' => 'announcement'])->first();
$postB = Post::addScopeAsSelect('is_not_announcement', ['ofType' => 'announcement'], false)->first();

$this->assertTrue($postA->is_announcement)
$this->assertFalse($postB->is_not_announcement);
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Other Laravel packages

* [`Laravel Analytics Event Tracking`](https://github.com/protonemedia/laravel-analytics-event-tracking): Laravel package to easily send events to Google Analytics.
* [`Laravel Blade On Demand`](https://github.com/protonemedia/laravel-blade-on-demand): Laravel package to compile Blade templates in memory.
* [`Laravel Cross Eloquent Search`](https://github.com/protonemedia/laravel-cross-eloquent-search): Laravel package to search through multiple Eloquent models.
* [`Laravel FFMpeg`](https://github.com/protonemedia/laravel-ffmpeg): This package provides an integration with FFmpeg for Laravel. The storage of the files is handled by Laravel's Filesystem.
* [`Laravel Form Components`](https://github.com/protonemedia/laravel-form-components): Blade components to rapidly build forms with Tailwind CSS Custom Forms and Bootstrap 4. Supports validation, model binding, default values, translations, includes default vendor styling and fully customizable!
* [`Laravel Paddle`](https://github.com/protonemedia/laravel-paddle): Paddle.com API integration for Laravel with support for webhooks/events.
* [`Laravel Verify New Email`](https://github.com/protonemedia/laravel-verify-new-email): This package adds support for verifying new email addresses: when a user updates its email address, it won't replace the old one until the new one is verified.
* [`Laravel WebDAV`](https://github.com/protonemedia/laravel-webdav): WebDAV driver for Laravel's Filesystem.

### Security

If you discover any security related issues, please email pascal@protone.media instead of using the issue tracker.

## Credits

- [Pascal Baljet](https://github.com/protonemedia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/pascalbaljetmedia/laravel-eloquent-scope-as-select) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.
