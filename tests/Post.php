<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeTitleIs($query, $title)
    {
        $query->where($query->qualifyColumn('title'), $title);
    }

    public function scopeTitleIsFoo($query)
    {
        $query->titleIs('foo');
    }

    public function scopeHasMoreCommentsThan($query, $value)
    {
        $query->has('comments', '>', $value);
    }

    public function scopeHasSixOrMoreComments($query)
    {
        $query->hasMoreCommentsThan(5);
    }
}
