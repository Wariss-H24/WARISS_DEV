<?php

use App\Enums\PageKind;
use App\Enums\PublicationStatus;
use App\Models\Pages\Page;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('shows only pages that are published with a past publication date', function () {
    $visible = Page::factory()->published()->create();
    Page::factory()->create();
    Page::factory()->scheduled()->create();
    Page::factory()->create(['status' => PublicationStatus::Archived]);
    Page::factory()->create(['status' => PublicationStatus::Published, 'published_at' => now()->addHour()]);

    expect(Page::published()->pluck('id')->all())->toBe([$visible->id]);
});

it('makes a scheduled page public once its publication date has passed', function () {
    $page = Page::factory()->create([
        'status' => PublicationStatus::Published,
        'published_at' => now()->addHour(),
    ]);

    expect($page->isPubliclyVisible())->toBeFalse();

    $this->travel(2)->hours();

    expect($page->isPubliclyVisible())->toBeTrue()
        ->and(Page::published()->count())->toBe(1);
});

it('rejects a published or scheduled page without a publication date', function (string $status) {
    $page = Page::factory()->create();

    DB::table('pages')->where('id', $page->id)->update(['status' => $status, 'published_at' => null]);
})->with(['published', 'scheduled'])->throws(QueryException::class, 'pages_published_at_check');

it('rejects an unknown status or kind at the database level', function (string $column, string $value, string $constraint) {
    $page = Page::factory()->create();

    expect(fn () => DB::table('pages')->where('id', $page->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'statut inconnu' => ['status', 'online', 'pages_status_check'],
    'nature inconnue' => ['kind', 'landing', 'pages_kind_check'],
]);

it('refuses to delete a system page, even permanently', function (string $method) {
    $page = Page::factory()->system('contact')->create();

    $page->{$method}();
})->with(['delete', 'forceDelete'])->throws(LogicException::class, 'contact');

it('soft deletes a custom page and keeps its slug reserved', function () {
    $page = Page::factory()->create(['fr' => ['title' => 'Mentions', 'slug' => 'mentions']]);

    $page->delete();

    expect(Page::count())->toBe(0)
        ->and(Page::withTrashed()->count())->toBe(1)
        ->and(fn () => Page::factory()->create(['fr' => ['title' => 'Autre', 'slug' => 'mentions']]))
        ->toThrow(QueryException::class, 'page_tr_locale_slug_unique');
});

it('allows several system pages without slug', function () {
    Page::factory()->system('home')->create();
    Page::factory()->system('contact')->create();

    expect(DB::table('page_translations')->whereNull('slug')->count())->toBe(4);
});

it('rejects two pages with the same key', function () {
    Page::factory()->system('home')->create();

    Page::factory()->system('home')->create();
})->throws(QueryException::class, 'pages_key_unique');

it('does not allow the kind to be mass assigned', function () {
    $page = new Page(['key' => 'piege', 'kind' => 'system']);

    expect($page->kind)->toBeNull()
        ->and($page->isSystem())->toBeFalse();
});

it('creates a custom page by default when no kind is given', function () {
    $page = Page::forceCreate(['key' => 'nouvelle-page']);

    expect($page->refresh()->kind)->toBe(PageKind::Custom)
        ->and($page->status)->toBe(PublicationStatus::Draft);
});
