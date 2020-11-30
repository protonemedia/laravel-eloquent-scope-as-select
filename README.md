# Laravel Eloquent Scope as Select

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-eloquent-scope-as-select)
![run-tests](https://github.com/protonemedia/laravel-eloquent-scope-as-select/workflows/run-tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://scrutinizer-ci.com/g/protonemedia/laravel-eloquent-scope-as-select)
[![Total Downloads](https://img.shields.io/packagist/dt/protonemedia/laravel-eloquent-scope-as-select.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-eloquent-scope-as-select)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen)](https://plant.treeware.earth/protonemedia/laravel-eloquent-scope-as-select)

...

## Requirements

* PHP 7.4+
* MySQL 5.7+
* Laravel 7.0 and higher

## Features

* Zero third-party dependencies

## Blogpost

If you want to know more about the background of this package, please read [the blogpost](https://protone.media/blog/search-through-multiple-eloquent-models-with-our-latest-laravel-package).

## Installation

You can install the package via composer:

```bash
composer require protonemedia/laravel-eloquent-scope-as-select
```

## Usage

In your `AppServiceProvider`:

```php
use ProtoneMedia\LaravelEloquentScopeAsSelect\ScopeAsSelect;

ScopeAsSelect::addMacro();

// or

ScopeAsSelect::addMacro('customMacroName');
```

Post model example:

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeHasFiveCommentsOrMore($query)
    {
        $query->has('comments', '>=', 5);
    }
}
```

```php
$postsWithAtLeastFiveComments = Post::hasFiveCommentsOrMore()->get();
```

Select all `Post` records and add the scope as a select:

```php
$allPosts = Post::query()
    ->addScopeAsSelect('has_five_comments_or_more', function ($query) {
        $query->hasFiveCommentsOrMore();
    })
    ->get();

$postsWithAtLeastFiveComments = $allPosts->filter->has_five_comments_or_more;
```

It works with inline query constraints as well:

```php
Post::query()
    ->addScopeAsSelect('title_is_foo_and_has_five_comments_or_more', function ($query) {
        $query->where('title', 'foo')->has('comments', '>=', 5);
    })
    ->orderBy('id')
    ->get();
```

You can add multiple selects:

```php
$allPosts = Post::query()
    ->addScopeAsSelect('title_is_foo', function ($query) {
        $query->where('title', 'foo');
    })
    ->addScopeAsSelect('has_five_comments_or_more', function ($query) {
        $query->hasFiveCommentsOrMore();
    })
    ->get();
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
