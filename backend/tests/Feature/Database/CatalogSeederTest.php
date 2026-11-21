<?php

use App\Models\Catalog\SkillCategory;
use App\Models\Catalog\Technology;
use App\Models\Catalog\TechnologyCategory;
use Database\Seeders\CatalogSeeder;

it('creates the starting categories in French and English, in order', function () {
    $this->seed(CatalogSeeder::class);

    $databases = TechnologyCategory::whereTranslation('slug', 'bases-de-donnees', 'fr')->sole();

    expect(TechnologyCategory::count())->toBe(7)
        ->and(SkillCategory::count())->toBe(6)
        ->and($databases->translate('en')->name)->toBe('Databases')
        ->and($databases->translate('en')->slug)->toBe('databases')
        ->and(TechnologyCategory::ordered()->first()->translate('fr')->name)->toBe('Langages');
});

it('does not duplicate nor overwrite categories when it runs again', function () {
    $this->seed(CatalogSeeder::class);
    $languages = TechnologyCategory::whereTranslation('slug', 'langages', 'fr')->sole();
    $languages->translate('fr')->name = 'Langages de programmation';
    $languages->save();

    $this->seed(CatalogSeeder::class);

    expect(TechnologyCategory::count())->toBe(7)
        ->and($languages->refresh()->translate('fr')->name)->toBe('Langages de programmation');
});

it('does not invent any technology', function () {
    $this->seed(CatalogSeeder::class);

    expect(Technology::count())->toBe(0);
});
