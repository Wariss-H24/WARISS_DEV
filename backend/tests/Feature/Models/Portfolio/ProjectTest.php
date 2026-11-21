<?php

use App\Enums\MediableRole;
use App\Enums\ProjectKind;
use App\Enums\ProjectStage;
use App\Models\Catalog\Skill;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectFeature;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a translated project with its nature and development stage', function () {
    $project = Project::factory()->create([
        'kind' => ProjectKind::Freelance,
        'development_stage' => ProjectStage::Maintained,
    ]);
    $project = Project::with('translations')->find($project->id);

    app()->setLocale('en');

    expect($project)
        ->kind->toBe(ProjectKind::Freelance)
        ->development_stage->toBe(ProjectStage::Maintained)
        ->role->toBe('Full-Stack Developer')
        ->started_on->toDateString()->toBe('2025-01-15');
});

it('shows only projects that are published with a past publication date', function () {
    $published = Project::factory()->published()->create();
    Project::factory()->create();

    expect(Project::published()->pluck('id')->all())->toBe([$published->id]);
});

it('rejects an end date before the start date', function () {
    Project::factory()->create(['started_on' => '2025-06-01', 'ended_on' => '2025-01-01']);
})->throws(QueryException::class, 'projects_dates_check');

it('rejects dangerous project links', function (string $column) {
    Project::factory()->create([$column => 'javascript:alert(1)']);
})->with(['live_url', 'repository_url', 'demo_url'])->throws(QueryException::class, '_url_check');

it('rejects an unknown nature or stage at the database level', function (string $column, string $value, string $constraint) {
    $project = Project::factory()->create();

    expect(fn () => DB::table('projects')->where('id', $project->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'nature inconnue' => ['kind', 'hobby', 'projects_kind_check'],
    'avancement inconnu' => ['development_stage', 'done', 'projects_development_stage_check'],
]);

it('refuses to delete a category that still contains projects', function () {
    $project = Project::factory()->create();

    $project->category->delete();
})->throws(QueryException::class, 'projects_project_category_id_foreign');

it('soft deletes a project and keeps its slug reserved', function () {
    $project = Project::factory()->create();
    $slug = $project->translate('fr')->slug;

    $project->delete();

    expect(fn () => Project::factory()->create(['fr' => [
        'title' => 'Autre', 'slug' => $slug, 'summary' => 'Résumé', 'description' => 'Texte', 'role' => 'Rôle',
    ]]))->toThrow(QueryException::class, 'project_tr_locale_slug_unique');
});

it('lists the visible features of a project in their display order', function () {
    $project = Project::factory()->create();
    $second = ProjectFeature::factory()->for($project)->create(['position' => 2]);
    $first = ProjectFeature::factory()->for($project)->create(['position' => 1]);
    ProjectFeature::factory()->for($project)->hidden()->create(['position' => 0]);

    expect($project->features()->visible()->ordered()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('links a project to the technologies used and the skills it illustrates', function () {
    $project = Project::factory()->create();
    $technology = Technology::factory()->create();
    $skill = Skill::factory()->create();

    $project->technologies()->attach($technology);
    $project->skills()->attach($skill);

    expect($project->technologies()->pluck('technologies.id')->all())->toBe([$technology->id])
        ->and($project->skills()->pluck('skills.id')->all())->toBe([$skill->id])
        ->and(fn () => $project->technologies()->attach($technology))->toThrow(QueryException::class, 'project_technology_pkey');
});

it('removes features, links and texts when a project is permanently deleted', function () {
    $project = Project::factory()->create();
    ProjectFeature::factory()->for($project)->create();
    $project->technologies()->attach(Technology::factory()->create());
    $project->skills()->attach(Skill::factory()->create());

    $project->forceDelete();

    foreach (['project_translations', 'project_features', 'project_feature_translations', 'project_technology', 'project_skill'] as $table) {
        $this->assertDatabaseCount($table, 0);
    }
});

it('builds an ordered gallery stored with the short morph alias', function () {
    $project = Project::factory()->create();
    $second = Media::factory()->create();
    $first = Media::factory()->create();
    $attachment = Media::factory()->privateDocument()->create();

    $project->attachMedia($second, MediableRole::Gallery, 2);
    $project->attachMedia($first, MediableRole::Gallery, 1);
    $project->attachMedia($attachment, MediableRole::Attachment);

    expect($project->gallery()->pluck('media.id')->all())->toBe([$first->id, $second->id])
        ->and($project->attachedMedia()->count())->toBe(3)
        ->and(DB::table('mediables')->distinct()->pluck('mediable_type')->all())->toBe(['project'])
        ->and(fn () => $first->delete())->toThrow(QueryException::class, 'mediables_media_id_foreign');
});

it('keeps the project and clears its cover when the cover media is deleted', function () {
    $cover = Media::factory()->create();
    $project = Project::factory()->create(['cover_media_id' => $cover->id]);

    $cover->delete();

    expect($project->refresh()->cover_media_id)->toBeNull();
});
