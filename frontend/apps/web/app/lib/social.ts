/**
 * Réseaux sociaux affichés sur le site public.
 *
 * Source principale : les liens du profil, gérés dans l'administration (Identité > Réseaux sociaux).
 * Tant qu'aucun lien n'y est enregistré, ces liens par défaut (adresses publiques choisies par le
 * propriétaire) sont affichés.
 */
import type { SocialLink, Site } from '@wariss/api-client';

export const DEFAULT_SOCIAL_LINKS: SocialLink[] = [
  { id: -1, profile_id: 0, platform: 'linkedin', url: 'https://www.linkedin.com/in/bada-wariss-a5a419395' },
  { id: -2, profile_id: 0, platform: 'github', url: 'https://github.com/Wariss-H24' },
  { id: -3, profile_id: 0, platform: 'gitlab', url: 'https://gitlab.com/Wariss-H24' },
];

const PLATFORM_LABELS: Record<string, string> = {
  linkedin: 'LinkedIn',
  github: 'GitHub',
  gitlab: 'GitLab',
  x: 'X',
  twitter: 'X',
  youtube: 'YouTube',
  mastodon: 'Mastodon',
  bluesky: 'Bluesky',
  stackoverflow: 'Stack Overflow',
  codepen: 'CodePen',
  email: 'E-mail',
  rss: 'RSS',
  website: 'Site web',
};

/** Liens à afficher : ceux du profil s'il y en a, sinon les liens par défaut. */
export function socialLinksOf(site: Site | null): SocialLink[] {
  const links = site?.profile?.social_links ?? [];

  return links.length ? links : DEFAULT_SOCIAL_LINKS;
}

/** Nom lisible d'une plateforme (« GitHub », « LinkedIn »…). */
export function platformLabel(platform: string): string {
  return PLATFORM_LABELS[platform.toLowerCase()] ?? platform;
}
