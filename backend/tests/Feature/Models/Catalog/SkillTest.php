<?php

use App\Models\Catalog\Skill;
use App\Models\Catalog\SkillCategory;
use App\Models\Catalog\Technology;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('falls back to French when the skill has no English translation', function () {
    $skill = Skill::factory()->create(['fr' => ['name' => "Conception d'API REST"]]);
    $skill->deleteTranslations('en');
    $skill = Skill::with('translations')->find($skill->id);

    app()->setLocale('en');

    expect($skill->name)->toBe("Conception d'API REST");
});

it('rejects a translation without a name', function () {
    Skill::factory()->create(['en' => ['name' => null]]);
})->throws(QueryException::class, 'skill_translations');

it('links a skill to the technologies used to practise it, in both directions', function () {
    $skill = Skill::factory()->create();
    $laravel = Technology::factory()->create(['name' => 'Laravel']);
    $postgres = Technology::factory()->create(['name' => 'PostgreSQL']);

    $skill->technologies()->attach([$laravel->id, $postgres->id]);

    expect($skill->technologies()->pluck('name')->sort()->values()->all())->toBe(['Laravel', 'PostgreSQL'])
        ->and($laravel->skills()->pluck('skills.id')->all())->toBe([$skill->id]);
});

it('rejects linking the same technology twice to a skill', function () {
    $skill = Skill::factory()->create();
    $technology = Technology::factory()->create();
    $skill->technologies()->attach($technology);

    $skill->technologies()->attach($technology);
})->throws(QueryException::class, 'skill_technology_pkey');

it('removes the links when a skill or a technology is deleted', function () {
    $skill = Skill::factory()->create();
    $technology = Technology::factory()->create();
    $skill->technologies()->attach($technology);

    $technology->delete();

    $this->assertDatabaseMissing('skill_technology', ['skill_id' => $skill->id]);
});

it('rejects invalid proficiency or experience at the database level', function (string $column, int|float $value, string $constraint) {
    $skill = Skill::factory()->create();

    expect(fn () => DB::table('skills')->where('id', $skill->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'niveau 0' => ['proficiency', 0, 'skills_proficiency_check'],
    'niveau 6' => ['proficiency', 6, 'skills_proficiency_check'],
    'expérience négative' => ['years_of_experience', -1.5, 'skills_years_of_experience_check'],
]);

it('accepts a skill without proficiency nor years of experience', function () {
    $skill = Skill::factory()->create(['proficiency' => null, 'years_of_experience' => null]);

    expect($skill->refresh())
        ->proficiency->toBeNull()
        ->years_of_experience->toBeNull();
});

it('refuses to delete a category that still contains skills', function () {
    $skill = Skill::factory()->create();

    $skill->category->delete();
})->throws(QueryException::class, 'skills_skill_category_id_foreign');

it('deletes the translations of a deleted skill', function () {
    $skill = Skill::factory()->create();

    $skill->delete();

    $this->assertDatabaseMissing('skill_translations', ['skill_id' => $skill->id]);
});

it('lists only visible skills, by position', function () {
    $second = Skill::factory()->create(['position' => 2]);
    $first = Skill::factory()->create(['position' => 1]);
    Skill::factory()->hidden()->create(['position' => 0]);

    expect(Skill::visible()->ordered()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('stores the short morph alias for skills', function () {
    expect((new Skill)->getMorphClass())->toBe('skill');
});

it('lists only active skill categories', function () {
    $active = SkillCategory::factory()->create();
    SkillCategory::factory()->inactive()->create();

    expect(SkillCategory::active()->pluck('id')->all())->toBe([$active->id]);
});
