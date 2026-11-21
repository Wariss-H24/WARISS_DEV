<?php

namespace Database\Seeders;

use App\Enums\PageKind;
use App\Enums\PageSectionType;
use App\Enums\PublicationStatus;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use Illuminate\Database\Seeder;

/**
 * Pages système (routes fixes du cahier des charges, §21.2 bis) et sections de l'accueil (§21.3).
 *
 * Toutes les pages sont créées en brouillon : elles sont publiées depuis l'administration
 * quand leur contenu est prêt (les pages légales doivent être rédigées selon la situation réelle).
 * Aucun texte n'est inventé : les sections n'ont pas de texte, le frontend affiche ses textes par défaut.
 * Idempotent : une page existante (même clé) n'est pas modifiée.
 */
class PageSeeder extends Seeder
{
    /**
     * @var array<string, array{fr: string, en: string}>
     */
    private const SYSTEM_PAGES = [
        'home' => ['fr' => 'Accueil', 'en' => 'Home'],
        'about' => ['fr' => 'À propos', 'en' => 'About'],
        'skills' => ['fr' => 'Compétences', 'en' => 'Skills'],
        'experience' => ['fr' => 'Parcours', 'en' => 'Experience'],
        'projects' => ['fr' => 'Projets', 'en' => 'Projects'],
        'services' => ['fr' => 'Services', 'en' => 'Services'],
        'lab' => ['fr' => 'Laboratoire', 'en' => 'Lab'],
        'blog' => ['fr' => 'Blog', 'en' => 'Blog'],
        'achievements' => ['fr' => 'Certifications et réalisations', 'en' => 'Certifications and achievements'],
        'testimonials' => ['fr' => 'Témoignages', 'en' => 'Testimonials'],
        'resume' => ['fr' => 'CV', 'en' => 'Resume'],
        'contact' => ['fr' => 'Contact', 'en' => 'Contact'],
        'terminal' => ['fr' => 'Terminal', 'en' => 'Terminal'],
        'now' => ['fr' => 'En ce moment', 'en' => 'Now'],
        'uses' => ['fr' => 'Mon environnement', 'en' => 'Uses'],
        'legal-notice' => ['fr' => 'Mentions légales', 'en' => 'Legal notice'],
        'privacy' => ['fr' => 'Confidentialité', 'en' => 'Privacy'],
    ];

    /**
     * Sections de l'accueil, dans l'ordre du cahier des charges (§21.3).
     * Les articles récents restent désactivés jusqu'au blog (lot 2).
     *
     * @var list<array{type: PageSectionType, active: bool}>
     */
    private const HOME_SECTIONS = [
        ['type' => PageSectionType::Hero, 'active' => true],
        ['type' => PageSectionType::About, 'active' => true],
        ['type' => PageSectionType::Skills, 'active' => true],
        ['type' => PageSectionType::FeaturedProjects, 'active' => true],
        ['type' => PageSectionType::Experiences, 'active' => true],
        ['type' => PageSectionType::Services, 'active' => true],
        ['type' => PageSectionType::LatestPosts, 'active' => false],
        ['type' => PageSectionType::Cta, 'active' => true],
        ['type' => PageSectionType::Contact, 'active' => true],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::SYSTEM_PAGES as $key => $titles) {
            if (Page::withTrashed()->where('key', $key)->exists()) {
                continue;
            }

            $page = new Page([
                'key' => $key,
                'template' => $key,
                'status' => PublicationStatus::Draft,
                'fr' => ['title' => $titles['fr']],
                'en' => ['title' => $titles['en']],
            ]);
            $page->kind = PageKind::System;
            $page->save();

            if ($key === 'home') {
                $this->seedHomeSections($page);
            }
        }
    }

    private function seedHomeSections(Page $home): void
    {
        foreach (self::HOME_SECTIONS as $position => $section) {
            PageSection::create([
                'page_id' => $home->id,
                'type' => $section['type'],
                'position' => $position,
                'is_active' => $section['active'],
                'config' => [],
            ]);
        }
    }
}
