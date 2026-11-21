<?php

use App\Enums\MediableRole;
use App\Models\Blog\Post;
use App\Models\Career\Certification;
use App\Models\Catalog\Skill;
use App\Models\Lab\Experiment;
use App\Models\Media\Media;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use App\Models\Pages\PageSectionItem;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Seo\SeoMetadata;
use App\Models\Services\Service;

/*
| Les tables polymorphes (mediables, seo_metadata, page_section_items) n'ont pas de clé étrangère
| vers leur contenu : les traits de nettoyage les mettent à jour à la suppression définitive.
*/

function deletePermanently(object $content): void
{
    method_exists($content, 'forceDelete') ? $content->forceDelete() : $content->delete();
}

it('frees the attached media when a content is permanently deleted', function (string $model) {
    $content = $model::factory()->create();
    $media = Media::factory()->create();
    $content->attachMedia($media, MediableRole::Gallery);

    deletePermanently($content);

    $media->delete();
    $this->assertDatabaseCount('mediables', 0);
    $this->assertModelMissing($media);
})->with([Project::class, Service::class, CaseStudy::class, Experiment::class, Post::class]);

it('keeps the attached media of a soft deleted content', function () {
    $project = Project::factory()->create();
    $project->attachMedia(Media::factory()->create(), MediableRole::Gallery);

    $project->delete();

    $this->assertDatabaseCount('mediables', 1);
});

it('removes the SEO record when a content is permanently deleted', function (string $model) {
    $content = $model::factory()->create();
    SeoMetadata::factory()->forContent($content)->create();

    deletePermanently($content);

    $this->assertDatabaseCount('seo_metadata', 0);
    $this->assertDatabaseCount('seo_metadata_translations', 0);
})->with([Page::class, Service::class, Project::class, CaseStudy::class, Experiment::class, Post::class]);

it('removes the featuring of a content permanently deleted, and keeps the others', function (string $model) {
    $section = PageSection::factory()->create();
    $content = $model::factory()->create();
    $other = $model::factory()->create();
    foreach ([$content, $other] as $position => $featured) {
        PageSectionItem::create([
            'page_section_id' => $section->id,
            'itemable_type' => $featured->getMorphClass(),
            'itemable_id' => $featured->id,
            'position' => $position,
        ]);
    }

    deletePermanently($content);

    expect($section->items()->pluck('itemable_id')->all())->toBe([$other->id]);
})->with([Project::class, Service::class, Post::class, Skill::class, Certification::class]);
