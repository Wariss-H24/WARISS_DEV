<?php

namespace Database\Seeders;

use App\Models\Catalog\SkillCategory;
use App\Models\Catalog\TechnologyCategory;
use App\Models\Portfolio\ProjectCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Catégories de départ des technologies (D4), des compétences (D5) et des projets (D6),
 * en français et en anglais.
 *
 * Idempotent : une catégorie déjà présente (même slug français) n'est pas modifiée.
 * Les technologies, compétences et projets eux-mêmes ne sont pas créés : ils doivent refléter
 * ce que le propriétaire a réellement réalisé et sont saisis dans l'administration.
 */
class CatalogSeeder extends Seeder
{
    /**
     * Natures de projets du cahier des charges (§9.4).
     *
     * @var list<array{fr: string, en: string, fr_slug: string, en_slug: string}>
     */
    private const PROJECT_CATEGORIES = [
        ['fr' => 'Applications web', 'en' => 'Web applications', 'fr_slug' => 'applications-web', 'en_slug' => 'web-applications'],
        ['fr' => 'E-commerce', 'en' => 'E-commerce', 'fr_slug' => 'e-commerce', 'en_slug' => 'e-commerce'],
        ['fr' => 'Plateformes éducatives', 'en' => 'Educational platforms', 'fr_slug' => 'plateformes-educatives', 'en_slug' => 'educational-platforms'],
        ['fr' => 'Outils de gestion', 'en' => 'Management tools', 'fr_slug' => 'outils-de-gestion', 'en_slug' => 'management-tools'],
        ['fr' => 'Applications mobiles', 'en' => 'Mobile applications', 'fr_slug' => 'applications-mobiles', 'en_slug' => 'mobile-applications'],
    ];

    /**
     * @var list<array{fr: string, en: string, fr_slug: string, en_slug: string}>
     */
    private const TECHNOLOGY_CATEGORIES = [
        ['fr' => 'Langages', 'en' => 'Languages', 'fr_slug' => 'langages', 'en_slug' => 'languages'],
        ['fr' => 'Frameworks', 'en' => 'Frameworks', 'fr_slug' => 'frameworks', 'en_slug' => 'frameworks'],
        ['fr' => 'Bibliothèques', 'en' => 'Libraries', 'fr_slug' => 'bibliotheques', 'en_slug' => 'libraries'],
        ['fr' => 'Bases de données', 'en' => 'Databases', 'fr_slug' => 'bases-de-donnees', 'en_slug' => 'databases'],
        ['fr' => 'Outils', 'en' => 'Tools', 'fr_slug' => 'outils', 'en_slug' => 'tools'],
        ['fr' => 'Plateformes', 'en' => 'Platforms', 'fr_slug' => 'plateformes', 'en_slug' => 'platforms'],
        ['fr' => 'Infrastructure et déploiement', 'en' => 'Infrastructure and deployment', 'fr_slug' => 'infrastructure-et-deploiement', 'en_slug' => 'infrastructure-and-deployment'],
    ];

    /**
     * @var list<array{fr: string, en: string, fr_slug: string, en_slug: string}>
     */
    private const SKILL_CATEGORIES = [
        ['fr' => 'Frontend', 'en' => 'Frontend', 'fr_slug' => 'frontend', 'en_slug' => 'frontend'],
        ['fr' => 'Backend', 'en' => 'Backend', 'fr_slug' => 'backend', 'en_slug' => 'backend'],
        ['fr' => 'Bases de données', 'en' => 'Databases', 'fr_slug' => 'bases-de-donnees', 'en_slug' => 'databases'],
        ['fr' => 'Conception et architecture', 'en' => 'Design and architecture', 'fr_slug' => 'conception-et-architecture', 'en_slug' => 'design-and-architecture'],
        ['fr' => 'Outils et méthodes', 'en' => 'Tools and methods', 'fr_slug' => 'outils-et-methodes', 'en_slug' => 'tools-and-methods'],
        ['fr' => 'Compétences professionnelles', 'en' => 'Professional skills', 'fr_slug' => 'competences-professionnelles', 'en_slug' => 'professional-skills'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories(TechnologyCategory::class, self::TECHNOLOGY_CATEGORIES);
        $this->seedCategories(SkillCategory::class, self::SKILL_CATEGORIES);
        $this->seedCategories(ProjectCategory::class, self::PROJECT_CATEGORIES);
    }

    /**
     * @param  class-string<TechnologyCategory|SkillCategory|ProjectCategory>  $model
     * @param  list<array{fr: string, en: string, fr_slug: string, en_slug: string}>  $categories
     */
    private function seedCategories(string $model, array $categories): void
    {
        foreach ($categories as $position => $category) {
            if ($model::whereTranslation('slug', $category['fr_slug'], 'fr')->exists()) {
                continue;
            }

            /** @var Model $instance */
            $instance = new $model([
                'position' => $position,
                'is_active' => true,
                'fr' => ['name' => $category['fr'], 'slug' => $category['fr_slug']],
                'en' => ['name' => $category['en'], 'slug' => $category['en_slug']],
            ]);
            $instance->save();
        }
    }
}
