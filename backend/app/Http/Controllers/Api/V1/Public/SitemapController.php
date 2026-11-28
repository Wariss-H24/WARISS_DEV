<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\PageKind;
use App\Http\Controllers\Controller;
use App\Models\Blog\Post;
use App\Models\Lab\Experiment;
use App\Models\Pages\Page;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Seo\SeoMetadata;
use App\Models\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Sitemap XML du site public (D16), généré depuis la base à chaque demande et servi par le site
 * sous /sitemap.xml. Contient les pages et contenus publiés, dans chaque langue traduite, avec
 * leurs alternatives de langue (hreflang) ; un contenu marqué « ne pas indexer » en est exclu.
 *
 * Adresses (D-03) : page système /:lang/<clé> (accueil : /:lang), page personnalisée
 * /:lang/pages/:slug (D-19), contenus /:lang/<segment>/:slug.
 */
class SitemapController extends Controller
{
    /**
     * Contenus publiés : segment d'URL => modèle.
     *
     * @var array<string, class-string<Project|CaseStudy|Service|Experiment|Post>>
     */
    private const CONTENTS = [
        'projects' => Project::class,
        'case-studies' => CaseStudy::class,
        'services' => Service::class,
        'lab' => Experiment::class,
        'blog' => Post::class,
    ];

    public function __invoke(): Response
    {
        $sitemap = Sitemap::create();
        $notIndexed = SeoMetadata::query()->where('robots_index', false)->get(['seoable_type', 'seoable_id'])
            ->map(fn (SeoMetadata $seo): string => "{$seo->seoable_type}:{$seo->seoable_id}")
            ->flip();

        foreach (Page::query()->published()->with('translations')->get() as $page) {
            $this->add($sitemap, $page, $notIndexed->all(), fn (string $locale, ?string $slug): ?string => match (true) {
                $page->kind === PageKind::System => $page->key === 'home' ? "/{$locale}" : "/{$locale}/{$page->key}",
                $slug !== null => "/{$locale}/pages/{$slug}",
                default => null,
            });
        }

        foreach (self::CONTENTS as $segment => $contentClass) {
            foreach ($contentClass::query()->published()->with('translations')->get() as $content) {
                $this->add($sitemap, $content, $notIndexed->all(), fn (string $locale, ?string $slug): ?string => $slug === null ? null : "/{$locale}/{$segment}/{$slug}");
            }
        }

        return response($sitemap->render(), Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Ajoute une adresse par langue traduite, chacune avec toutes ses alternatives.
     *
     * @param  array<string, int>  $notIndexed
     * @param  callable(string, ?string): ?string  $pathFor
     */
    private function add(Sitemap $sitemap, Model $content, array $notIndexed, callable $pathFor): void
    {
        if (isset($notIndexed["{$content->getMorphClass()}:{$content->getKey()}"])) {
            return;
        }

        $siteUrl = (string) config('wariss.site_url');
        $paths = [];

        /** @var iterable<Model> $translations */
        $translations = $content->getRelationValue('translations');

        foreach ($translations as $translation) {
            $locale = (string) $translation->getAttribute('locale');
            $path = $pathFor($locale, $translation->getAttribute('slug'));

            if ($path !== null) {
                $paths[$locale] = $siteUrl.$path;
            }
        }

        foreach ($paths as $url) {
            $tag = Url::create($url)->setLastModificationDate($content->getAttribute('updated_at') ?? now());

            foreach ($paths as $alternateLocale => $alternateUrl) {
                $tag->addAlternate($alternateUrl, $alternateLocale);
            }

            $sitemap->add($tag);
        }
    }
}
