import type { Experience, Paginated, Project, TechnologyCategory } from '@wariss/api-client';
import { Container, Section } from '@wariss/ui';
import { useTranslation } from 'react-i18next';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/terminal';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Terminal } from '~/features/terminal/Terminal';
import { apiGetOr } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const empty = { data: [], meta: { current_page: 1, last_page: 1, per_page: 0, total: 0 } };
  const [projects, technologies, experiences] = await Promise.all([
    apiGetOr<Paginated<Project>>('/projects', locale, empty, { per_page: 8 }),
    apiGetOr<{ data: TechnologyCategory[] }>('/technologies', locale, { data: [] }),
    apiGetOr<{ data: Experience[] }>('/experiences', locale, { data: [] }),
  ]);

  return { locale, projects: projects.data, technologies: technologies.data.flatMap((category) => category.technologies), experiences: experiences.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: 'Terminal',
    description: locale === 'fr' ? 'Explorez mon parcours en ligne de commande.' : 'Explore my work from the command line.',
    paths: staticPaths('terminal'),
  });
}

export default function TerminalPage() {
  const { locale, ...data } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const { t } = useTranslation();

  return (
    <>
      <PageHeader
        eyebrow="~/terminal"
        title={t('nav.terminal')}
        description={locale === 'fr' ? 'Explorez mon parcours en ligne de commande : tapez « help ».' : 'Explore my work from the command line: type “help”.'}
      />
      <Section>
        <Container>
          <Terminal locale={locale} data={{ profile: layout?.site?.profile ?? null, ...data }} />
        </Container>
      </Section>
    </>
  );
}
