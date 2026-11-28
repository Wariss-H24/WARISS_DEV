import type { Experience, Paginated, Project, TechnologyCategory } from '@wariss/api-client';
import { Container, GradientText, Section } from '@wariss/ui';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/terminal';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Terminal } from '~/features/terminal/Terminal';
import { apiGetOr } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';
import styles from './terminal.module.css';

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

const HEADER = {
  fr: {
    eyebrow: '~/terminal · mode développeur',
    before: 'Explorez mon portfolio',
    highlight: 'en ligne de commande',
    description:
      'Une autre façon de découvrir mon travail : projets, compétences et parcours, directement depuis un terminal. Rien à installer, tout se passe dans votre navigateur.',
    steps: [
      { keys: ['help', 'Entrée ↵'], label: 'liste toutes les commandes' },
      { keys: ['↑', '↓'], label: 'parcourt l’historique' },
      { keys: ['Tab'], label: 'complète la commande' },
      { keys: ['clear'], label: 'efface l’écran' },
    ],
    stepsLabel: 'Raccourcis du terminal',
  },
  en: {
    eyebrow: '~/terminal · developer mode',
    before: 'Explore my portfolio',
    highlight: 'from the command line',
    description: 'Another way to discover my work: projects, skills and experience, straight from a terminal. Nothing to install, it all runs in your browser.',
    steps: [
      { keys: ['help', 'Enter ↵'], label: 'lists every command' },
      { keys: ['↑', '↓'], label: 'browses the history' },
      { keys: ['Tab'], label: 'completes the command' },
      { keys: ['clear'], label: 'clears the screen' },
    ],
    stepsLabel: 'Terminal shortcuts',
  },
} as const;

export default function TerminalPage() {
  const { locale, ...data } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const header = HEADER[locale];

  return (
    <>
      <PageHeader
        eyebrow={
          <>
            <span className={styles.pulse} aria-hidden="true" />
            {header.eyebrow}
          </>
        }
        title={
          <>
            {header.before} <GradientText>{header.highlight}</GradientText>
          </>
        }
        description={header.description}
      >
        <ul className={styles.steps} aria-label={header.stepsLabel}>
          {header.steps.map((step) => (
            <li key={step.label} className={styles.step}>
              <span className={styles.keys}>
                {step.keys.map((key, index) => (
                  <span key={key} className={styles.keyGroup}>
                    {index > 0 ? (
                      <span className={styles.plus} aria-hidden="true">
                        +
                      </span>
                    ) : null}
                    <kbd className={styles.key}>{key}</kbd>
                  </span>
                ))}
              </span>
              <span className={styles.stepLabel}>{step.label}</span>
            </li>
          ))}
        </ul>
      </PageHeader>
      <Section>
        <Container size="wide">
          <Terminal locale={locale} data={{ profile: layout?.site?.profile ?? null, ...data }} />
        </Container>
      </Section>
    </>
  );
}
