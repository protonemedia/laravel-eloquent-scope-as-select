<?php declare(strict_types=1);

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

class ScopeAsSelectTest extends TestCase
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
    public function it_can_add_a_scope_as_a_select()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', fn ($query) => $query->titleIsFoo())
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);
    }

    /** @test */
    public function it_can_add_a_scope_as_a_select_and_cast_inversed()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', fn ($query) => $query->titleIsFoo(), false)
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo);
        $this->assertTrue($posts->get(1)->title_is_foo);
    }

    /** @test */
    public function it_can_add_a_scope_by_using_the_name()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', 'titleIsFoo')
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);
    }

    /** @test */
    public function it_can_add_multiple_scopes_by_using_an_array()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo_and_has_six_comments_or_more', ['titleIsFoo', 'hasSixOrMoreComments'])
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo_and_has_six_comments_or_more);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(2)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(3)->title_is_foo_and_has_six_comments_or_more);
    }

    /** @test */
    public function it_can_add_multiple_dynamic_scopes_by_using_an_array()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo_and_has_more_than_five_comments', [
                'titleIsFoo',
                'hasMoreCommentsThan' => 5,
            ])
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo_and_has_more_than_five_comments);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_more_than_five_comments);
        $this->assertFalse($posts->get(2)->title_is_foo_and_has_more_than_five_comments);
        $this->assertFalse($posts->get(3)->title_is_foo_and_has_more_than_five_comments);
    }

    /** @test */
    public function it_can_add_multiple_dynamic_scopes_by_using_an_array_of_scope_arguments()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo_and_has_more_than_five_comments', [
                'titleIsFoo',
                'hasMoreCommentsThan' => [5],
            ])
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo_and_has_more_than_five_comments);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_more_than_five_comments);
        $this->assertFalse($posts->get(2)->title_is_foo_and_has_more_than_five_comments);
        $this->assertFalse($posts->get(3)->title_is_foo_and_has_more_than_five_comments);
    }

    /** @test */
    public function it_can_add_multiple_and_has_relation_scopes()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        foreach (range(1, 5) as $i) {
            $postA->comments()->create(['body' => 'ok']);
        }

        foreach (range(1, 10) as $i) {
            $postB->comments()->create(['body' => 'ok']);
        }

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', function ($query) {
                $query->titleIsFoo();
            })
            ->addScopeAsSelect('has_six_or_more_comments', function ($query) {
                $query->hasSixOrMoreComments();
            })
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);

        $this->assertFalse($posts->get(0)->has_six_or_more_comments);
        $this->assertTrue($posts->get(1)->has_six_or_more_comments);
    }

    /** @test */
    public function it_can_do_inline_contraints_as_well()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo_and_has_six_comments_or_more', function ($query) {
                $query->where('title', 'foo')->has('comments', '>=', 6);
            })
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo_and_has_six_comments_or_more);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(2)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(3)->title_is_foo_and_has_six_comments_or_more);
    }

    /** @test */
    public function it_can_mix_scopes_outside_of_the_closure()
    {
        [$postA, $postB, $postC, $postD] = $this->prepareFourPosts();

        $posts = Post::query()
            ->where('title', 'foo')
            ->addScopeAsSelect('title_is_foo_and_has_six_comments_or_more', function ($query) {
                $query->has('comments', '>=', 6);
            })
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $posts);
        $this->assertTrue($posts->contains($postA));
        $this->assertTrue($posts->contains($postB));
        $this->assertFalse($posts->get(0)->title_is_foo_and_has_six_comments_or_more);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_six_comments_or_more);
    }
}
