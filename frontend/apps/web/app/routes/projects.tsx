import type { Category, Paginated, Project } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/projects';
import { CategoryFilter } from '~/components/CategoryFilter/CategoryFilter';
import { ProjectCard } from '~/features/content/cards';
import { ListingPage } from '~/features/content/ListingPage';
import { apiGet, apiGetOr } from '~/lib/api.server';
import { layoutSite, pageParam, routeLocale, searchParam, siteName, siteUrlOf } from '~/lib/route-helpers';
import { pathTo } from '~/lib/routes';
import { buildMeta, staticPaths } from '~/lib/seo';

export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const category = searchParam(request, 'category');
  const [projects, categories] = await Promise.all([
    apiGet<Paginated<Project>>('/projects', locale, { page: pageParam(request), per_page: 12, category }),
    apiGetOr<{ data: Category[] }>('/taxonomies/project-categories', locale, { data: [] }),
  ]);

  return { locale, category, projects, categories: categories.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const site = layoutSite(matches);
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(site),
    locale,
    title: t('pages.projectsTitle'),
    description: t('pages.projectsDescription'),
    paths: staticPaths('projects'),
  });
}

export default function Projects() {
  const { locale, category, projects, categories } = useLoaderData<typeof loader>();
  const { t } = useTranslation();
  const base = pathTo(locale, 'projects');

  return (
    <ListingPage
      eyebrow={t('pages.projectsEyebrow')}
      title={t('pages.projectsTitle')}
      description={t('pages.projectsDescription')}
      filter={<CategoryFilter categories={categories} active={category} hrefFor={(slug) => (slug ? `${base}?category=${slug}` : base)} />}
      cards={projects.data.map((project) => (
        <ProjectCard key={project.id} project={project} locale={locale} headingLevel="h2" />
      ))}
      meta={projects.meta}
      clearHref={category ? base : null}
    />
  );
}
