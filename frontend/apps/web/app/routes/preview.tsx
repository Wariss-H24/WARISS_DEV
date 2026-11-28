import type { CaseStudy, Experiment, Page, Post, Project, Service } from '@wariss/api-client';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/preview';
import { CaseStudyView, ExperimentView, PostView, ProjectView, ServiceView } from '~/features/content/views';
import { CmsPage } from '~/features/pages/CmsPage';
import { apiGet } from '~/lib/api.server';
import { routeLocale } from '~/lib/route-helpers';
import type { loader as layoutLoader } from './lang-layout';

type PreviewType = 'page' | 'project' | 'case_study' | 'service' | 'experiment' | 'post';

/**
 * Aperçu d'un brouillon par lien signé (D-09) : le jeton est vérifié par l'API (signature,
 * expiration de 30 minutes). Jamais mis en cache, jamais indexé, jamais compté.
 */
export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const type = params.token.split('.')[0] as PreviewType;
  const { data } = await apiGet<{ data: Record<string, unknown> }>(`/preview/${encodeURIComponent(params.token)}`, locale);

  return { locale, type, item: data };
}

export function headers() {
  return { 'Cache-Control': 'no-store, private', 'X-Robots-Tag': 'noindex, nofollow' };
}

export const meta: Route.MetaFunction = ({ loaderData }) => [
  { title: `Aperçu · ${String(loaderData?.item.title ?? '')}` },
  { name: 'robots', content: 'noindex, nofollow' },
];

export default function Preview() {
  const { locale, type, item } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');

  switch (type) {
    case 'project':
      return <ProjectView project={item as unknown as Project} locale={locale} />;
    case 'case_study':
      return <CaseStudyView caseStudy={item as unknown as CaseStudy} locale={locale} />;
    case 'service':
      return <ServiceView service={item as unknown as Service} locale={locale} />;
    case 'experiment':
      return <ExperimentView experiment={item as unknown as Experiment} locale={locale} />;
    case 'post':
      return <PostView post={item as unknown as Post} locale={locale} />;
    default:
      return <CmsPage page={item as unknown as Page} locale={locale} profile={layout?.site?.profile ?? null} />;
  }
}
