import type { Achievement, TimelineEvent } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Badge, Card, Container, EmptyState, Section, SectionHeading } from '@wariss/ui';
import { ExternalLink, Trophy } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/achievements';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Reveal } from '~/components/Reveal/Reveal';
import { apiGetOr } from '~/lib/api.server';
import { formatDate } from '~/lib/format';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import styles from './experience.module.css';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const [achievements, timeline] = await Promise.all([
    apiGetOr<{ data: Achievement[] }>('/achievements', locale, { data: [] }),
    apiGetOr<{ data: TimelineEvent[] }>('/timeline', locale, { data: [] }),
  ]);

  return { locale, achievements: achievements.data, timeline: timeline.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.achievementsTitle'),
    description: t('pages.achievementsDescription'),
    paths: staticPaths('achievements'),
  });
}

/** Titre d'un événement de chronologie : sa surcharge, sinon celui de sa source. */
function eventTitle(event: TimelineEvent): string {
  const source = event.eventable as Record<string, unknown> | null | undefined;

  return (
    event.title ?? (source?.title as string | undefined) ?? (source?.job_title as string | undefined) ?? (source?.degree_title as string | undefined) ?? ''
  );
}

export default function Achievements() {
  const { locale, achievements, timeline } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <>
      <PageHeader eyebrow={t('nav.achievements')} title={t('pages.achievementsTitle')} description={t('pages.achievementsDescription')} />
      <Section>
        <Container size="wide">
          {achievements.length ? (
            <ul className={styles.grid}>
              {achievements.map((achievement, index) => (
                <Reveal as="li" key={achievement.id} delay={Math.min(index, 5) * 0.05}>
                  <Card padded spotlight className={styles.card}>
                    <Trophy aria-hidden="true" className={styles.icon} />
                    <Badge mono>{achievement.type.replace('_', ' ')}</Badge>
                    <h2 className={styles.role}>{achievement.title}</h2>
                    <p className={styles.period}>
                      {formatDate(achievement.achieved_on, locale)}
                      {achievement.organization_name ? ` · ${achievement.organization_name}` : ''}
                    </p>
                    {achievement.description ? <p className={styles.summary}>{achievement.description}</p> : null}
                    {achievement.url ? (
                      <a href={achievement.url} target="_blank" rel="noopener noreferrer" className={styles.verify}>
                        {t('actions.readMore')}
                        <ExternalLink aria-hidden="true" size={13} />
                      </a>
                    ) : null}
                  </Card>
                </Reveal>
              ))}
            </ul>
          ) : (
            <EmptyState icon={<Trophy aria-hidden="true" size={22} />} title={t('pages.empty')} />
          )}
        </Container>
      </Section>

      {timeline.length ? (
        <Section alt aria-labelledby="timeline-title">
          <Container size="wide">
            <SectionHeading id="timeline-title" title={t('pages.timeline')} />
            <ol className={styles.timeline}>
              {timeline.map((event) => (
                <li key={event.id} className={styles.entry}>
                  <span className={styles.marker} aria-hidden="true">
                    <span style={{ width: 8, height: 8, borderRadius: 999, background: 'var(--color-accent)' }} />
                  </span>
                  <div>
                    <p className={styles.period}>{formatDate(event.event_date, locale, 'month')}</p>
                    <h3 className={styles.role}>{eventTitle(event)}</h3>
                    {event.description ? <p className={styles.summary}>{event.description}</p> : null}
                  </div>
                </li>
              ))}
            </ol>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
