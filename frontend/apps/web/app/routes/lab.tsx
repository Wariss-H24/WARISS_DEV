import type { Category, Experiment, Paginated } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/lab';
import { CategoryFilter } from '~/components/CategoryFilter/CategoryFilter';
import { ExperimentCard } from '~/features/content/cards';
import { ListingPage } from '~/features/content/ListingPage';
import { apiGet, apiGetOr } from '~/lib/api.server';
import { layoutSite, pageParam, routeLocale, searchParam, siteName, siteUrlOf } from '~/lib/route-helpers';
import { pathTo } from '~/lib/routes';
import { buildMeta, staticPaths } from '~/lib/seo';

export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const category = searchParam(request, 'category');
  const [experiments, categories] = await Promise.all([
    apiGet<Paginated<Experiment>>('/lab', locale, { page: pageParam(request), per_page: 12, category }),
    apiGetOr<{ data: Category[] }>('/taxonomies/experiment-categories', locale, { data: [] }),
  ]);

  return { locale, category, experiments, categories: categories.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.labTitle'),
    description: t('pages.labDescription'),
    paths: staticPaths('lab'),
  });
}

export default function Lab() {
  const { locale, category, experiments, categories } = useLoaderData<typeof loader>();
  const { t } = useTranslation();
  const base = pathTo(locale, 'lab');

  return (
    <ListingPage
      eyebrow={t('pages.labEyebrow')}
      title={t('pages.labTitle')}
      description={t('pages.labDescription')}
      filter={<CategoryFilter categories={categories} active={category} hrefFor={(slug) => (slug ? `${base}?category=${slug}` : base)} />}
      cards={experiments.data.map((experiment) => (
        <ExperimentCard key={experiment.id} experiment={experiment} locale={locale} headingLevel="h2" />
      ))}
      meta={experiments.meta}
      clearHref={category ? base : null}
    />
  );
}
