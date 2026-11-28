import type { Experiment } from '@wariss/api-client';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/experiment';
import { ExperimentView } from '~/features/content/views';
import { loadDetail } from '~/lib/detail.server';
import { layoutSite, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta } from '~/lib/seo';

export function loader({ params }: Route.LoaderArgs) {
  return loadDetail<Experiment>('lab', 'lab', 'experiment', params);
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  if (!loaderData) {
    return [];
  }

  const { item, locale, alternates } = loaderData;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: item.title,
    description: item.summary,
    paths: alternates,
    image: item.cover,
    seo: item.seo,
  });
}

export default function ExperimentPage() {
  const { item, locale } = useLoaderData<typeof loader>();

  return <ExperimentView experiment={item} locale={locale} />;
}
