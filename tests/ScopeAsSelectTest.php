<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

class ScopeAsSelectTest extends TestCase
{
    /** @test */
    public function it_can_add_a_scope_as_a_select()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', function ($query) {
                $query->titleIsFoo();
            })
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);
    }
}
