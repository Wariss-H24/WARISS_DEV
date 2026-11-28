import type { Category, Paginated, Service } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/services';
import { CategoryFilter } from '~/components/CategoryFilter/CategoryFilter';
import { ServiceCard } from '~/features/content/cards';
import { ListingPage } from '~/features/content/ListingPage';
import { apiGet, apiGetOr } from '~/lib/api.server';
import { layoutSite, pageParam, routeLocale, searchParam, siteName, siteUrlOf } from '~/lib/route-helpers';
import { pathTo } from '~/lib/routes';
import { buildMeta, staticPaths } from '~/lib/seo';

export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const category = searchParam(request, 'category');
  const [services, categories] = await Promise.all([
    apiGet<Paginated<Service>>('/services', locale, { page: pageParam(request), per_page: 12, category }),
    apiGetOr<{ data: Category[] }>('/taxonomies/service-categories', locale, { data: [] }),
  ]);

  return { locale, category, services, categories: categories.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.servicesTitle'),
    description: t('pages.servicesDescription'),
    paths: staticPaths('services'),
  });
}

export default function Services() {
  const { locale, category, services, categories } = useLoaderData<typeof loader>();
  const { t } = useTranslation();
  const base = pathTo(locale, 'services');

  return (
    <ListingPage
      eyebrow={t('pages.servicesEyebrow')}
      title={t('pages.servicesTitle')}
      description={t('pages.servicesDescription')}
      filter={<CategoryFilter categories={categories} active={category} hrefFor={(slug) => (slug ? `${base}?category=${slug}` : base)} />}
      cards={services.data.map((service) => (
        <ServiceCard key={service.id} service={service} locale={locale} headingLevel="h2" />
      ))}
      meta={services.meta}
      clearHref={category ? base : null}
    />
  );
}
