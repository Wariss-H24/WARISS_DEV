<?php

use App\Models\Media\Media;
use App\Models\Portfolio\Project;
use App\Models\Seo\Redirect;
use App\Models\Seo\SeoMetadata;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('attaches one translated SEO record to a content', function () {
    $project = Project::factory()->create();
    SeoMetadata::factory()->forContent($project)->create([
        'robots_index' => false,
        'fr' => ['meta_title' => 'Plateforme e-commerce'],
        'en' => ['meta_title' => 'E-commerce platform'],
    ]);

    app()->setLocale('en');
    $seo = $project->seo()->with('translations')->sole();

    expect($seo->meta_title)->toBe('E-commerce platform')
        ->and($seo->robotsDirective())->toBe('noindex, follow')
        ->and($seo->seoable->is($project))->toBeTrue()
        ->and(DB::table('seo_metadata')->value('seoable_type'))->toBe('project');
});

it('rejects a second SEO record for the same content', function () {
    $project = Project::factory()->create();
    SeoMetadata::factory()->forContent($project)->create();

    SeoMetadata::factory()->forContent($project)->create();
})->throws(QueryException::class, 'seo_metadata_seoable_type_seoable_id_unique');

it('rejects invalid SEO values at the database level', function (array $values, string $constraint) {
    $seo = SeoMetadata::factory()->create();

    expect(fn () => DB::table('seo_metadata')->where('id', $seo->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'type de contenu non prévu' => [['seoable_type' => 'user'], 'seo_metadata_seoable_type_check'],
    'URL canonique dangereuse' => [['canonical_url' => 'javascript:alert(1)'], 'seo_metadata_canonical_url_check'],
    'données structurées en liste' => [fn () => ['structured_data' => DB::raw("'[1]'::jsonb")], 'seo_metadata_structured_data_check'],
]);

it('keeps the SEO record and clears its social image when the image is deleted', function () {
    $image = Media::factory()->create();
    $seo = SeoMetadata::factory()->create(['og_image_media_id' => $image->id]);

    $image->delete();

    expect($seo->refresh()->og_image_media_id)->toBeNull();
});

it('counts the uses of an active redirect', function () {
    $redirect = Redirect::factory()->create();

    $redirect->recordHit();
    $redirect->recordHit();

    expect($redirect->refresh()->hits)->toBe(2)
        ->and(Redirect::active()->count())->toBe(1);
});

it('rejects unsafe or inconsistent redirects', function (array $values, string $constraint) {
    expect(fn () => Redirect::factory()->create($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'code non prévu' => [['status_code' => 303], 'redirects_status_code_check'],
    'origine externe' => [['from_path' => 'https://ancien-site.example/page'], 'redirects_from_path_check'],
    'origine relative au protocole' => [['from_path' => '//ancien-site.example'], 'redirects_from_path_check'],
    'destination dangereuse' => [['to_path' => 'javascript:alert(1)'], 'redirects_to_path_check'],
    'destination relative au protocole' => [['to_path' => '//site-malveillant.example'], 'redirects_to_path_check'],
    'vers elle-même' => [['from_path' => '/fr/a', 'to_path' => '/fr/a'], 'redirects_not_to_itself_check'],
]);

it('accepts a redirect to an internal path or an https URL', function (string $to) {
    $redirect = Redirect::factory()->create(['to_path' => $to]);

    expect($redirect->refresh()->to_path)->toBe($to);
})->with(['/fr/projects/nouveau', 'https://github.com/wariss']);

it('rejects two redirects from the same path', function () {
    Redirect::factory()->create(['from_path' => '/fr/ancien']);

    Redirect::factory()->create(['from_path' => '/fr/ancien']);
})->throws(QueryException::class, 'redirects_from_path_unique');
