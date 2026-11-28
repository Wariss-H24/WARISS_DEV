/**
 * Chargement d'un contenu publié par son slug, commun aux pages de détail.
 *
 * L'API trouve un contenu par son slug dans n'importe quelle langue : si l'adresse utilise le slug
 * d'une autre langue (/en/projects/slug-francais), on redirige (301) vers le slug de la langue
 * demandée, pour une seule URL canonique par langue.
 */
import type { PublicContentType, Slugged } from '@wariss/api-client';
import { redirect } from 'react-router';
import { apiGet } from './api.server';
import { routeLocale } from './route-helpers';
import type { ContentSegment } from './routes';
import { localizedPaths } from './seo';

export async function loadDetail<T extends Slugged & { id: number }>(
  apiSegment: string,
  urlSegment: ContentSegment,
  trackingType: PublicContentType,
  params: { lang?: string; slug?: string },
) {
  const locale = routeLocale(params);
  const { data: item } = await apiGet<{ data: T }>(`/${apiSegment}/${encodeURIComponent(params.slug ?? '')}`, locale);
  const expected = item.slugs[locale];

  if (expected && expected !== params.slug) {
    throw redirect(`/${locale}/${urlSegment}/${expected}`, 301);
  }

  return {
    locale,
    item,
    alternates: localizedPaths(urlSegment, item.slugs),
    tracking: { type: trackingType, id: item.id },
  };
}
