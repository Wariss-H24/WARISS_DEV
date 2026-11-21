<?php

use App\Enums\ExperimentKind;
use App\Models\Catalog\Technology;
use App\Models\Lab\Experiment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores an embedded demo designated by a whitelisted component key', function () {
    $experiment = Experiment::factory()->create(['kind' => ExperimentKind::Visualization, 'demo_component_key' => 'sorting-visualizer-2']);

    expect($experiment->refresh())
        ->kind->toBe(ExperimentKind::Visualization)
        ->demo_component_key->toBe('sorting-visualizer-2');
});

it('rejects a component key that could carry code or a path', function (string $key) {
    Experiment::factory()->create(['demo_component_key' => $key]);
})->with([
    'balise script' => ['<script>alert(1)</script>'],
    'chemin relatif' => ['../../etc/passwd'],
    'majuscules' => ['ColorPalette'],
    'espaces' => ['color palette'],
    'tiret final' => ['palette-'],
    'tirets doubles' => ['color--palette'],
])->throws(QueryException::class, 'experiments_demo_component_key_check');

it('rejects dangerous demo, repository or video links', function (string $column) {
    Experiment::factory()->create([$column => 'javascript:alert(1)']);
})->with(['demo_url', 'repository_url', 'video_url'])->throws(QueryException::class, '_url_check');

it('rejects an unknown kind at the database level', function () {
    $experiment = Experiment::factory()->create();

    DB::table('experiments')->where('id', $experiment->id)->update(['kind' => 'game']);
})->throws(QueryException::class, 'experiments_kind_check');

it('shows only published experiments', function () {
    $published = Experiment::factory()->published()->create();
    Experiment::factory()->create();

    expect(Experiment::published()->pluck('id')->all())->toBe([$published->id]);
});

it('finds an experiment with the full text search', function () {
    $experiment = Experiment::factory()->create(['fr' => [
        'title' => 'Générateur de palettes', 'slug' => 'generateur-palettes',
        'summary' => 'Couleurs accessibles', 'description' => 'Calcule les contrastes WCAG.',
    ]]);
    Experiment::factory()->create();

    expect(Experiment::search('generateur contrastes', 'fr')->pluck('id')->all())->toBe([$experiment->id]);
});

it('reuses technologies and refuses to delete a category still in use', function () {
    $experiment = Experiment::factory()->create();
    $technology = Technology::factory()->create();
    $experiment->technologies()->attach($technology);

    expect($experiment->technologies()->pluck('technologies.id')->all())->toBe([$technology->id])
        ->and(fn () => $experiment->category->delete())->toThrow(QueryException::class, 'experiments_experiment_category_id_foreign');
});
