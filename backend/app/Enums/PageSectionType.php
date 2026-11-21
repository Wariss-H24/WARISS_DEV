<?php

namespace App\Enums;

/**
 * Type d'une section de page (domaine D18) : chaque valeur correspond à un composant React.
 * Valeurs reprises dans la contrainte CHECK page_sections_type_check.
 */
enum PageSectionType: string
{
    case Hero = 'hero';
    case About = 'about';
    case Skills = 'skills';
    case FeaturedProjects = 'featured_projects';
    case Experiences = 'experiences';
    case Services = 'services';
    case LatestPosts = 'latest_posts';
    case Cta = 'cta';
    case Contact = 'contact';
    case Richtext = 'richtext';
    case Gallery = 'gallery';
    case Faq = 'faq';
}
