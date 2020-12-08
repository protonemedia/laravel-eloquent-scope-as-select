<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

class WhereNotTest extends TestCase
{
    private function prepareFourPosts(): array
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'foo']);
        $postC = Post::create(['title' => 'bar']);
        $postD = Post::create(['title' => 'bar']);

        foreach (range(1, 5) as $i) {
            $postA->comments()->create(['body' => 'ok']);
            $postC->comments()->create(['body' => 'ok']);
        }

        foreach (range(1, 10) as $i) {
            $postB->comments()->create(['body' => 'ok']);
            $postD->comments()->create(['body' => 'ok']);
        }

        return [$postA, $postB, $postC, $postD];
    }

    /** @test */
    public function it_can_invert_a_scope()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->whereNot(fn ($query) => $query->titleIsFoo())
            ->orderBy('id')
            ->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($postB));
    }

    /** @test */
    public function it_can_invert_a_scope_by_using_the_name()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->whereNot('titleIsFoo')
            ->orderBy('id')
            ->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($postB));
    }

    /** @test */
    public function it_can_invert_multiple_scopes_by_using_an_array()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->whereNot(['titleIsFoo', 'hasSixOrMoreComments'])
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $posts);
        $this->assertTrue($posts->contains($postA));
        $this->assertTrue($posts->contains($postC));
        $this->assertTrue($posts->contains($postD));
    }

    /** @test */
    public function it_can_invert_multiple_dynamic_scopes_by_using_an_array()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->whereNot([
                'titleIsFoo',
                'hasMoreCommentsThan' => 5,
            ])
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $posts);
        $this->assertTrue($posts->contains($postA));
        $this->assertTrue($posts->contains($postC));
        $this->assertTrue($posts->contains($postD));
    }

    /** @test */
    public function it_can_invert_multiple_dynamic_scopes_by_using_an_array_of_scope_arguments()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->whereNot([
                'titleIsFoo',
                'hasMoreCommentsThan' => [5],
            ])
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $posts);
        $this->assertTrue($posts->contains($postA));
        $this->assertTrue($posts->contains($postC));
        $this->assertTrue($posts->contains($postD));
    }

    /** @test */
    public function it_can_invert_multiple_and_has_relation_scopes()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        foreach (range(1, 10) as $i) {
            $postA->comments()->create(['body' => 'ok']);
        }

        foreach (range(1, 5) as $i) {
            $postB->comments()->create(['body' => 'ok']);
        }

        $posts = Post::query()
            ->whereNot(function ($query) {
                $query->hasSixOrMoreComments();
            })
            ->whereNot(function ($query) {
                $query->titleIsFoo();
            })
            ->orderBy('id')
            ->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($postB));
    }

    /** @test */
    public function it_can_invert_inline_contraints_as_well()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->whereNot(function ($query) {
                $query->where('title', 'foo')->has('comments', '>=', 6);
            })
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $posts);
        $this->assertTrue($posts->contains($postA));
        $this->assertTrue($posts->contains($postC));
        $this->assertTrue($posts->contains($postD));
    }

    /** @test */
    public function it_can_mix_scopes_outside_of_the_closure()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->where('title', 'foo')
            ->whereNot(function ($query) {
                $query->has('comments', '>=', 6);
            })
            ->orderBy('id')
            ->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($postA));
    }
}
