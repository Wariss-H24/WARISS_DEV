/**
 * Balises SEO d'une page (architecture §4.6) : titre, description, URL canonique, hreflang pour
 * chaque langue et x-default, Open Graph et Twitter dans la langue de la page. La fiche SEO saisie
 * dans l'administration (Seo) prime sur les valeurs par défaut.
 */
import type { Media, Seo } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import type { MetaDescriptor } from 'react-router';

export interface SeoInput {
  siteUrl: string;
  siteName: string;
  locale: Locale;
  title: string;
  description?: string | null;
  /** Chemin de la page dans chaque langue (/fr/projects/x, /en/projects/y). */
  paths: Partial<Record<Locale, string>>;
  image?: Media | null;
  type?: 'website' | 'article' | 'profile';
  publishedAt?: string | null;
  seo?: Seo | null;
  noindex?: boolean;
}

const OG_LOCALES: Record<Locale, string> = { fr: 'fr_FR', en: 'en_US' };

export function buildMeta(input: SeoInput): MetaDescriptor[] {
  const { siteUrl, siteName, locale, seo } = input;
  const title = seo?.meta_title || input.title;
  const fullTitle = title.includes(siteName) ? title : `${title} · ${siteName}`;
  const description = (seo?.meta_description || input.description || '').slice(0, 200);
  const canonical = seo?.canonical_url || `${siteUrl}${input.paths[locale] ?? `/${locale}`}`;
  const image = seo?.og_image ?? input.image ?? null;
  const indexable = !input.noindex && seo?.robots_index !== false;
  const followable = seo?.robots_follow !== false;
  const robots = `${indexable ? 'index' : 'noindex'}, ${followable ? 'follow' : 'nofollow'}`;

  const meta: MetaDescriptor[] = [
    { title: fullTitle },
    { name: 'description', content: description },
    { name: 'robots', content: robots },
    { tagName: 'link', rel: 'canonical', href: canonical },
    { property: 'og:site_name', content: siteName },
    { property: 'og:type', content: input.type ?? 'website' },
    { property: 'og:title', content: seo?.og_title || title },
    { property: 'og:description', content: seo?.og_description || description },
    { property: 'og:url', content: canonical },
    { property: 'og:locale', content: OG_LOCALES[locale] },
    { name: 'twitter:card', content: image ? 'summary_large_image' : 'summary' },
    { name: 'twitter:title', content: seo?.og_title || title },
    { name: 'twitter:description', content: seo?.og_description || description },
  ];

  for (const [code, path] of Object.entries(input.paths)) {
    if (path) {
      meta.push({ tagName: 'link', rel: 'alternate', hrefLang: code, href: `${siteUrl}${path}` });

      if (code !== locale) {
        meta.push({ property: 'og:locale:alternate', content: OG_LOCALES[code as Locale] });
      }
    }
  }

  const defaultPath = input.paths.fr ?? input.paths[locale];

  if (defaultPath) {
    meta.push({ tagName: 'link', rel: 'alternate', hrefLang: 'x-default', href: `${siteUrl}${defaultPath}` });
  }

  if (image) {
    meta.push({ property: 'og:image', content: image.url });
    meta.push({ name: 'twitter:image', content: image.url });

    if (image.width && image.height) {
      meta.push({ property: 'og:image:width', content: String(image.width) });
      meta.push({ property: 'og:image:height', content: String(image.height) });
    }

    if (image.alt) {
      meta.push({ property: 'og:image:alt', content: image.alt });
    }
  }

  if (input.publishedAt && input.type === 'article') {
    meta.push({ property: 'article:published_time', content: input.publishedAt });
  }

  if (seo?.structured_data) {
    meta.push({ 'script:ld+json': seo.structured_data });
  }

  return meta;
}

/** Chemins d'un contenu traduit dans chaque langue, à partir de ses slugs. */
export function localizedPaths(segment: string, slugs: Partial<Record<Locale, string | null>>): Partial<Record<Locale, string>> {
  const paths: Partial<Record<Locale, string>> = {};

  for (const [code, slug] of Object.entries(slugs)) {
    if (slug) {
      paths[code as Locale] = `/${code}/${segment}/${slug}`;
    }
  }

  return paths;
}

/** Chemins d'une page fixe dans chaque langue. */
export function staticPaths(key: string): Record<Locale, string> {
  return {
    fr: key === 'home' ? '/fr' : `/fr/${key}`,
    en: key === 'home' ? '/en' : `/en/${key}`,
  };
}
