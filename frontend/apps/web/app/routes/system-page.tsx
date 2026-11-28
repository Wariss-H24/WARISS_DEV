import type { Page } from '@wariss/api-client';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/system-page';
import { CmsPage } from '~/features/pages/CmsPage';
import { apiFind } from '~/lib/api.server';
import { resolveRedirectOr404 } from '~/lib/redirects.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';

/** Page système éditée dans l'administration, par sa clé : now, uses, legal-notice, privacy… */
export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);

  if (!/^[a-z0-9-]{1,60}$/.test(params.page)) {
    return resolveRedirectOr404(request);
  }

  const found = await apiFind<{ data: Page }>(`/pages/${params.page}`, locale);
  const page = found?.data;

  // Inconnue, ou page personnalisée (qui a sa propre adresse /pages/:slug) : redirection ou 404.
  if (!page || page.kind !== 'system' || page.key !== params.page) {
    return resolveRedirectOr404(request);
  }

  return { locale, page, tracking: { type: 'page' as const, id: page.id } };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  if (!loaderData) {
    return [];
  }

  const { page, locale } = loaderData;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: page.title,
    description: page.summary,
    paths: staticPaths(page.key ?? 'home'),
    seo: page.seo,
  });
}

export default function SystemPage() {
  const { page, locale } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');

  return <CmsPage page={page} locale={locale} profile={layout?.site?.profile ?? null} />;
}
