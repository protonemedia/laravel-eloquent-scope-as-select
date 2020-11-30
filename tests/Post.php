<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeTitleIsFoo($query)
    {
        $query->where($query->qualifyColumn('title'), 'foo');
    }

    public function scopeHasSixOrMoreComments($query)
    {
        $query->has('comments', '>=', 6);
    }
}
