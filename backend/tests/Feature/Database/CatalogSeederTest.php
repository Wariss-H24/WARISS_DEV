<?php

use App\Models\Catalog\Skill;
use App\Models\Catalog\SkillCategory;
use App\Models\Catalog\Technology;
use App\Models\Catalog\TechnologyCategory;
use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectCategory;
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

it('creates the project categories of the specification', function () {
    $this->seed(CatalogSeeder::class);

    $categories = ProjectCategory::ordered()->get();

    expect($categories)->toHaveCount(5)
        ->and($categories->first()->translate('fr')->name)->toBe('Applications web')
        ->and($categories->first()->translate('en')->slug)->toBe('web-applications');
});

it('does not invent any technology, skill nor project', function () {
    $this->seed(CatalogSeeder::class);

    expect(Technology::count())->toBe(0)
        ->and(Skill::count())->toBe(0)
        ->and(Project::count())->toBe(0);
});
