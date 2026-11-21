<?php

use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectTranslation;
use Illuminate\Support\Str;

function projectWithTexts(string $frTitle, string $frDescription, ?string $enTitle = null, string $enDescription = 'English description'): Project
{
    $enTitle ??= $frTitle.' (EN)';

    return Project::factory()->create([
        'fr' => ['title' => $frTitle, 'slug' => Str::slug($frTitle), 'summary' => 'Résumé', 'description' => $frDescription, 'role' => 'Développeur'],
        'en' => ['title' => $enTitle, 'slug' => Str::slug($enTitle), 'summary' => 'Summary', 'description' => $enDescription, 'role' => 'Developer'],
    ]);
}

it('finds a French project without typing the accents', function () {
    $project = projectWithTexts('Plateforme éducative', 'Gestion des élèves et des évaluations.');
    projectWithTexts('Boutique en ligne', 'Paiement et livraison.');

    expect(Project::search('educative evaluations', 'fr')->pluck('id')->all())->toBe([$project->id]);
});

it('matches the different forms of a French word', function () {
    $project = projectWithTexts('Applications web', 'Développement de plusieurs applications.');

    expect(Project::search('application', 'fr')->pluck('id')->all())->toBe([$project->id]);
});

it('searches only in the requested locale', function () {
    projectWithTexts('Tableau de bord', 'Statistiques.', 'Dashboard', 'Analytics.');

    expect(Project::search('dashboard', 'en')->count())->toBe(1)
        ->and(Project::search('dashboard', 'fr')->count())->toBe(0);
});

it('supports exact phrases and exclusions', function () {
    $api = projectWithTexts('API REST Laravel', 'Conception d\'une API.');
    projectWithTexts('API GraphQL Node', 'Conception d\'une API.');

    expect(Project::search('api -graphql', 'fr')->pluck('id')->all())->toBe([$api->id])
        ->and(Project::search('"api rest"', 'fr')->pluck('id')->all())->toBe([$api->id]);
});

it('uses the current locale by default', function () {
    projectWithTexts('Tableau de bord', 'Statistiques.', 'Dashboard', 'Analytics.');

    app()->setLocale('en');

    expect(Project::search('analytics')->count())->toBe(1);
});

it('never serializes the search vector', function () {
    $project = projectWithTexts('Plateforme', 'Texte');

    expect(array_keys(ProjectTranslation::where('project_id', $project->id)->first()->toArray()))
        ->not->toContain('search_vector');
});
