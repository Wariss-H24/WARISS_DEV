<?php

use App\Enums\CaseStudySectionType;
use App\Models\Catalog\Technology;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\CaseStudySection;
use App\Models\Portfolio\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('lists the case studies of a project', function () {
    $project = Project::factory()->create();
    $first = CaseStudy::factory()->for($project)->create();
    $second = CaseStudy::factory()->for($project)->create();

    expect($project->caseStudies()->pluck('id')->sort()->values()->all())->toBe([$first->id, $second->id]);
});

it('allows a case study without project', function () {
    $caseStudy = CaseStudy::factory()->create(['project_id' => null]);

    expect($caseStudy->refresh()->project)->toBeNull();
});

it('keeps the case study and clears its project when the project is permanently deleted', function () {
    $caseStudy = CaseStudy::factory()->create();

    $caseStudy->project->forceDelete();

    expect($caseStudy->refresh()->project_id)->toBeNull();
});

it('orders the sections in reading order with their translated content', function () {
    $caseStudy = CaseStudy::factory()->create();
    CaseStudySection::factory()->for($caseStudy)->create(['type' => CaseStudySectionType::Solution, 'position' => 2]);
    CaseStudySection::factory()->for($caseStudy)->create([
        'type' => CaseStudySectionType::Challenge,
        'position' => 1,
        'fr' => ['title' => 'Le défi', 'body' => 'Synchroniser les stocks.'],
        'en' => ['title' => 'The challenge', 'body' => 'Synchronising stock.'],
    ]);

    app()->setLocale('en');
    $sections = $caseStudy->sections()->with('translations')->get();

    expect($sections->pluck('type')->all())->toBe([CaseStudySectionType::Challenge, CaseStudySectionType::Solution])
        ->and($sections->first()->title)->toBe('The challenge');
});

it('rejects an unknown section type, an invalid reading time or inconsistent dates', function (string $table, array $values, string $constraint) {
    $section = CaseStudySection::factory()->create();
    $id = $table === 'case_studies' ? $section->case_study_id : $section->id;

    expect(fn () => DB::table($table)->where('id', $id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'type inconnu' => ['case_study_sections', ['type' => 'summary'], 'case_study_sections_type_check'],
    'temps de lecture nul' => ['case_studies', ['reading_time_minutes' => 0], 'case_studies_reading_time_check'],
    'dates inversées' => ['case_studies', ['started_on' => '2025-06-01', 'ended_on' => '2025-01-01'], 'case_studies_dates_check'],
]);

it('shows only published case studies', function () {
    $published = CaseStudy::factory()->published()->create();
    CaseStudy::factory()->create();

    expect(CaseStudy::published()->pluck('id')->all())->toBe([$published->id]);
});

it('removes sections, texts and technology links when a case study is permanently deleted', function () {
    $section = CaseStudySection::factory()->create();
    $section->caseStudy->technologies()->attach(Technology::factory()->create());

    $section->caseStudy->forceDelete();

    foreach (['case_study_translations', 'case_study_sections', 'case_study_section_translations', 'case_study_technology'] as $table) {
        $this->assertDatabaseCount($table, 0);
    }
});
