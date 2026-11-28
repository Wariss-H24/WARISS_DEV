/**
 * Adresses du site public (D-03, D-19) : segments en anglais, slugs traduits.
 */
import type { MenuItem } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';

export type RouteKey =
  | 'home'
  | 'about'
  | 'skills'
  | 'experience'
  | 'projects'
  | 'services'
  | 'lab'
  | 'blog'
  | 'achievements'
  | 'testimonials'
  | 'resume'
  | 'contact'
  | 'terminal'
  | 'now'
  | 'uses'
  | 'legal-notice'
  | 'privacy';

export type ContentSegment = 'projects' | 'case-studies' | 'services' | 'lab' | 'blog' | 'pages';

/** Chemin d'une page fixe : /fr, /fr/projects… */
export function pathTo(locale: Locale, key: RouteKey | string): string {
  return key === 'home' ? `/${locale}` : `/${locale}/${key}`;
}

/** Chemin d'un contenu : /fr/projects/mon-projet */
export function contentPath(locale: Locale, segment: ContentSegment, slug: string): string {
  return `/${locale}/${segment}/${slug}`;
}

/** Destination d'un élément de menu géré dans l'administration. */
export function menuItemHref(locale: Locale, item: MenuItem): { href: string; external: boolean } {
  if (item.type === 'external' && item.url) {
    return { href: item.url, external: /^https?:/i.test(item.url) };
  }

  if (item.type === 'page' && item.page) {
    if (item.page.key) {
      return { href: pathTo(locale, item.page.key), external: false };
    }

    if (item.page.slug) {
      return { href: contentPath(locale, 'pages', item.page.slug), external: false };
    }
  }

  return { href: pathTo(locale, item.route_key ?? 'home'), external: false };
}

/**
 * Chemin équivalent dans l'autre langue. Pour un contenu, le slug traduit est fourni par la page
 * (handle `alternates`) ; à défaut, on remplace simplement le préfixe de langue.
 */
export function switchLocalePath(pathname: string, target: Locale, alternates?: Partial<Record<Locale, string>>): string {
  const alternate = alternates?.[target];

  if (alternate) {
    return alternate;
  }

  return pathname.replace(/^\/(fr|en)(?=\/|$)/, `/${target}`) || `/${target}`;
}
