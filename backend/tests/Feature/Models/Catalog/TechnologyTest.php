<?php

use App\Enums\TechnologyProficiency;
use App\Models\Catalog\Technology;
use App\Models\Catalog\TechnologyCategory;
use App\Models\Media\Media;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a technology with its untranslated brand name and translated description', function () {
    $technology = Technology::factory()->create([
        'name' => 'PostgreSQL',
        'proficiency' => TechnologyProficiency::Proficient,
        'fr' => ['description' => 'Base de données relationnelle'],
        'en' => ['description' => 'Relational database'],
    ]);
    $technology = Technology::with('translations')->find($technology->id);

    app()->setLocale('en');

    expect($technology)
        ->name->toBe('PostgreSQL')
        ->description->toBe('Relational database')
        ->proficiency->toBe(TechnologyProficiency::Proficient);
});

it('rejects two technologies with the same slug', function () {
    Technology::factory()->create(['slug' => 'laravel']);

    Technology::factory()->create(['slug' => 'laravel']);
})->throws(QueryException::class, 'technologies_slug_unique');

it('rejects invalid values at the database level', function (string $column, string $value, string $constraint) {
    $technology = Technology::factory()->create();

    expect(fn () => DB::table('technologies')->where('id', $technology->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'niveau inconnu' => ['proficiency', 'guru', 'technologies_proficiency_check'],
    'couleur sans dièse' => ['brand_color', 'ff2d20', 'technologies_brand_color_check'],
    'couleur non hexadécimale' => ['brand_color', '#gg2d20', 'technologies_brand_color_check'],
]);

it('refuses to delete a category that still contains technologies', function () {
    $technology = Technology::factory()->create();

    $technology->category->delete();
})->throws(QueryException::class, 'technologies_technology_category_id_foreign');

it('rejects two categories with the same slug in the same locale', function () {
    TechnologyCategory::factory()->create(['fr' => ['name' => 'Outils', 'slug' => 'outils']]);

    TechnologyCategory::factory()->create(['fr' => ['name' => 'Outils bis', 'slug' => 'outils']]);
})->throws(QueryException::class, 'technology_category_tr_locale_slug_unique');

it('allows the same slug in two different locales', function () {
    $category = TechnologyCategory::factory()->create([
        'fr' => ['name' => 'Frameworks', 'slug' => 'frameworks'],
        'en' => ['name' => 'Frameworks', 'slug' => 'frameworks'],
    ]);

    expect($category->translations()->count())->toBe(2);
});

it('keeps the technology and clears its icon when the icon media is deleted', function () {
    $icon = Media::factory()->create();
    $technology = Technology::factory()->create(['icon_media_id' => $icon->id]);

    $icon->delete();

    expect($technology->refresh()->icon_media_id)->toBeNull();
});

it('lists only visible technologies, by position then creation order', function () {
    $second = Technology::factory()->create(['position' => 2]);
    Technology::factory()->hidden()->create(['position' => 0]);
    $first = Technology::factory()->create(['position' => 1]);
    $third = Technology::factory()->create(['position' => 2]);

    expect(Technology::visible()->ordered()->pluck('id')->all())
        ->toBe([$first->id, $second->id, $third->id]);
});

it('lists only active categories', function () {
    $active = TechnologyCategory::factory()->create();
    TechnologyCategory::factory()->inactive()->create();

    expect(TechnologyCategory::active()->pluck('id')->all())->toBe([$active->id]);
});
