<?php

use App\Enums\PostType;
use App\Models\Blog\Post;
use App\Models\Blog\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a translated Markdown post with its type, category and author', function () {
    $post = Post::factory()->create(['type' => PostType::Tutorial]);
    $post = Post::with(['translations', 'category', 'author'])->find($post->id);

    expect($post)
        ->type->toBe(PostType::Tutorial)
        ->content->toStartWith('## Introduction')
        ->and($post->author->isAdmin())->toBeTrue()
        ->and($post->category)->not->toBeNull();
});

it('does not allow the author to be mass assigned', function () {
    $post = new Post(['author_id' => 999]);

    expect($post->author_id)->toBeNull();
});

it('keeps the post and clears its author when the account is deleted', function () {
    $post = Post::factory()->create();

    $post->author->delete();

    expect($post->refresh()->author_id)->toBeNull();
});

it('shows only published posts, and a scheduled post once its date has passed', function () {
    $published = Post::factory()->published()->create();
    $scheduled = Post::factory()->scheduled()->create();
    Post::factory()->create();

    expect(Post::published()->pluck('id')->all())->toBe([$published->id]);

    $this->travel(2)->days();
    $scheduled->update(['status' => 'published']);

    expect(Post::published()->pluck('id')->sort()->values()->all())->toBe([$published->id, $scheduled->id]);
});

it('tags a post with several tags, without duplicates', function () {
    $post = Post::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $post->tags()->attach($tags);

    expect($post->tags()->count())->toBe(2)
        ->and($tags->first()->posts()->pluck('posts.id')->all())->toBe([$post->id])
        ->and(fn () => $post->tags()->attach($tags->first()))->toThrow(QueryException::class, 'post_tag_pkey');
});

it('rejects two tags with the same slug in the same locale', function () {
    Tag::factory()->create(['fr' => ['name' => 'Laravel', 'slug' => 'laravel']]);

    Tag::factory()->create(['fr' => ['name' => 'Laravel bis', 'slug' => 'laravel']]);
})->throws(QueryException::class, 'tag_tr_locale_slug_unique');

it('finds a post by the words of its Markdown content', function () {
    $post = Post::factory()->create(['fr' => [
        'title' => 'Sécuriser une API', 'slug' => 'securiser-api', 'excerpt' => 'Bonnes pratiques',
        'content' => "## Authentification\n\nUtiliser des jetons à durée limitée.",
    ]]);
    Post::factory()->create();

    expect(Post::search('jetons duree', 'fr')->pluck('id')->all())->toBe([$post->id]);
});

it('rejects an unknown type or a zero reading time at the database level', function (array $values, string $constraint) {
    $post = Post::factory()->create();

    expect(fn () => DB::table('posts')->where('id', $post->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'type inconnu' => [['type' => 'podcast'], 'posts_type_check'],
    'temps de lecture nul' => [['reading_time_minutes' => 0], 'posts_reading_time_check'],
]);

it('refuses to delete a category that still contains posts', function () {
    $post = Post::factory()->create();

    $post->category->delete();
})->throws(QueryException::class, 'posts_post_category_id_foreign');

it('removes the tag links and texts when a post is permanently deleted', function () {
    $post = Post::factory()->create();
    $post->tags()->attach(Tag::factory()->create());

    $post->forceDelete();

    $this->assertDatabaseCount('post_tag', 0);
    $this->assertDatabaseCount('post_translations', 0);
    expect(User::count())->toBe(1);
});
