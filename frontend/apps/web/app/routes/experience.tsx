import type { Certification, Education, Experience } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Badge, Card, Container, Section, SectionHeading } from '@wariss/ui';
import { BadgeCheck, Briefcase, ExternalLink, GraduationCap } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/experience';
import { Markdown } from '~/components/Markdown/Markdown';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Reveal } from '~/components/Reveal/Reveal';
import { TechList } from '~/components/TechList/TechList';
import { apiGetOr } from '~/lib/api.server';
import { formatDate, formatPeriod } from '~/lib/format';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import styles from './experience.module.css';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const [experiences, educations, certifications] = await Promise.all([
    apiGetOr<{ data: Experience[] }>('/experiences', locale, { data: [] }),
    apiGetOr<{ data: Education[] }>('/educations', locale, { data: [] }),
    apiGetOr<{ data: Certification[] }>('/certifications', locale, { data: [] }),
  ]);

  return { locale, experiences: experiences.data, educations: educations.data, certifications: certifications.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.experienceTitle'),
    description: t('pages.experienceDescription'),
    paths: staticPaths('experience'),
  });
}

export default function ExperiencePage() {
  const { locale, experiences, educations, certifications } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <>
      <PageHeader eyebrow={t('pages.experienceEyebrow')} title={t('pages.experienceTitle')} description={t('pages.experienceDescription')} />

      {experiences.length ? (
        <Section aria-labelledby="experiences-title">
          <Container size="wide">
            <SectionHeading id="experiences-title" title={t('pages.experiences')} />
            <ol className={styles.timeline}>
              {experiences.map((experience, index) => (
                <Reveal as="li" key={experience.id} delay={Math.min(index, 4) * 0.05} className={styles.entry}>
                  <span className={styles.marker} aria-hidden="true">
                    {experience.organization_logo ? <img src={experience.organization_logo.url} alt="" width={40} height={40} /> : <Briefcase size={18} />}
                  </span>
                  <Card padded className={styles.card}>
                    <div className={styles.head}>
                      <div>
                        <h3 className={styles.role}>{experience.job_title}</h3>
                        <p className={styles.org}>
                          {experience.organization_url ? (
                            <a href={experience.organization_url} target="_blank" rel="noopener noreferrer">
                              {experience.organization_name}
                              <ExternalLink aria-hidden="true" size={12} />
                            </a>
                          ) : (
                            experience.organization_name
                          )}
                        </p>
                      </div>
                      <div className={styles.badges}>
                        {experience.is_current ? (
                          <Badge tone="success" dot>
                            {t('meta.present')}
                          </Badge>
                        ) : null}
                        {experience.is_remote ? <Badge>{t('meta.remote')}</Badge> : null}
                      </div>
                    </div>
                    <p className={styles.period}>
                      {formatPeriod(experience.started_on, experience.ended_on, locale, t('meta.present'))}
                      {experience.location_label ? ` · ${experience.location_label}` : ''}
                    </p>
                    <p className={styles.summary}>{experience.summary}</p>
                    <Markdown>{experience.description}</Markdown>
                    {experience.achievements ? <Markdown>{experience.achievements}</Markdown> : null}
                    <TechList technologies={experience.technologies} />
                  </Card>
                </Reveal>
              ))}
            </ol>
          </Container>
        </Section>
      ) : null}

      {educations.length ? (
        <Section alt aria-labelledby="educations-title">
          <Container size="wide">
            <SectionHeading id="educations-title" title={t('pages.educations')} />
            <ul className={styles.grid}>
              {educations.map((education) => (
                <li key={education.id}>
                  <Card padded className={styles.card}>
                    <GraduationCap aria-hidden="true" className={styles.icon} />
                    <h3 className={styles.role}>{education.degree_title}</h3>
                    <p className={styles.org}>{education.institution_name}</p>
                    <p className={styles.period}>{formatPeriod(education.started_on, education.ended_on, locale, t('meta.present'))}</p>
                    {education.field_of_study ? <p className={styles.summary}>{education.field_of_study}</p> : null}
                    {education.description ? <Markdown>{education.description}</Markdown> : null}
                  </Card>
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}

      {certifications.length ? (
        <Section aria-labelledby="certifications-title">
          <Container size="wide">
            <SectionHeading id="certifications-title" title={t('pages.certifications')} />
            <ul className={styles.grid}>
              {certifications.map((certification) => (
                <li key={certification.id}>
                  <Card padded spotlight className={styles.card}>
                    <BadgeCheck aria-hidden="true" className={styles.icon} />
                    <h3 className={styles.role}>{certification.title}</h3>
                    <p className={styles.org}>{certification.issuer_name}</p>
                    <p className={styles.period}>
                      {t('pages.issuedOn', { date: formatDate(certification.issued_on, locale) })}
                      {certification.expires_on ? ` · ${t('pages.expiresOn', { date: formatDate(certification.expires_on, locale) })}` : ''}
                    </p>
                    {certification.description ? <p className={styles.summary}>{certification.description}</p> : null}
                    <TechList technologies={certification.technologies} />
                    {certification.verification_url ? (
                      <a href={certification.verification_url} target="_blank" rel="noopener noreferrer" className={styles.verify}>
                        {t('pages.verify')}
                        <ExternalLink aria-hidden="true" size={13} />
                      </a>
                    ) : null}
                  </Card>
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
