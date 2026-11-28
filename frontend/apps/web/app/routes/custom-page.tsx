import type { Page } from '@wariss/api-client';
import { redirect, useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/custom-page';
import { CmsPage } from '~/features/pages/CmsPage';
import { apiGet } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, localizedPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';

/** Page personnalisée (D-19) : /:lang/pages/:slug. */
export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const { data: page } = await apiGet<{ data: Page }>(`/pages/${encodeURIComponent(params.slug)}`, locale);
  const expected = page.slugs[locale];

  if (page.kind === 'system' && page.key) {
    throw redirect(`/${locale}/${page.key === 'home' ? '' : page.key}`, 301);
  }

  if (expected && expected !== params.slug) {
    throw redirect(`/${locale}/pages/${expected}`, 301);
  }

  return { locale, page, alternates: localizedPaths('pages', page.slugs), tracking: { type: 'page' as const, id: page.id } };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  if (!loaderData) {
    return [];
  }

  const { page, locale, alternates } = loaderData;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: page.title,
    description: page.summary,
    paths: alternates,
    seo: page.seo,
  });
}

export default function CustomPage() {
  const { page, locale } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');

  return <CmsPage page={page} locale={locale} profile={layout?.site?.profile ?? null} />;
}
