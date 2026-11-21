<?php

use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Models\Pages\PageSection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

dataset('dangerous urls', [
    'javascript' => ['javascript:alert(document.cookie)'],
    'javascript en majuscules' => ['JavaScript:alert(1)'],
    'data' => ['data:text/html,<script>alert(1)</script>'],
    'relative au protocole' => ['//site-malveillant.example'],
    'sans schéma' => ['www.example.com'],
]);

it('rejects a dangerous external media URL', function (string $url) {
    $media = Media::factory()->externalVideo()->create();

    DB::table('media')->where('id', $media->id)->update(['external_url' => $url]);
})->with('dangerous urls')->throws(QueryException::class, 'media_external_url_check');

it('rejects a dangerous technology URL', function (string $url) {
    $technology = Technology::factory()->create();

    DB::table('technologies')->where('id', $technology->id)->update(['official_url' => $url]);
})->with('dangerous urls')->throws(QueryException::class, 'technologies_official_url_check');

it('rejects a dangerous call-to-action URL', function (string $url) {
    $section = PageSection::factory()->create();

    DB::table('page_section_translations')->where('page_section_id', $section->id)->update(['cta_url' => $url]);
})->with('dangerous urls')->throws(QueryException::class, 'page_section_translations_cta_url_check');

it('accepts safe call-to-action URLs', function (string $url) {
    $section = PageSection::factory()->create();

    DB::table('page_section_translations')->where('page_section_id', $section->id)->update(['cta_url' => $url]);

    expect(DB::table('page_section_translations')->where('page_section_id', $section->id)->pluck('cta_url')->unique()->all())
        ->toBe([$url]);
})->with([
    'https' => ['https://wariss.dev/fr/contact'],
    'chemin interne' => ['/fr/contact'],
    'racine' => ['/'],
    'ancre' => ['#contact'],
    'e-mail' => ['mailto:contact@example.com'],
]);
