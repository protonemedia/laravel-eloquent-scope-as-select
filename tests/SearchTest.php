<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

class SearchTest extends TestCase
{
    /** @test */
    public function it_can_search_two_models_and_orders_by_updated_at_by_default()
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
