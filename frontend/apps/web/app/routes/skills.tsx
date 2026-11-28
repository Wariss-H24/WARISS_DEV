import type { SkillCategory, TechnologyCategory } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Card, Container, EmptyState, Section, SectionHeading } from '@wariss/ui';
import { Layers } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/skills';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Reveal } from '~/components/Reveal/Reveal';
import { TechList } from '~/components/TechList/TechList';
import { apiGetOr } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import styles from './skills.module.css';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const [skills, technologies] = await Promise.all([
    apiGetOr<{ data: SkillCategory[] }>('/skills', locale, { data: [] }),
    apiGetOr<{ data: TechnologyCategory[] }>('/technologies', locale, { data: [] }),
  ]);

  return { locale, skills: skills.data, technologies: technologies.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.skillsTitle'),
    description: t('pages.skillsDescription'),
    paths: staticPaths('skills'),
  });
}

const PROFICIENCY_LEVEL = { learning: 1, working: 2, proficient: 3, expert: 4 } as const;

/** Niveau visuel de 1 à 5, annoncé en texte aux lecteurs d'écran. */
function Level({ value, max = 5 }: { value: number; max?: number }) {
  return (
    <span className={styles.level} role="img" aria-label={`${value} / ${max}`}>
      {Array.from({ length: max }, (_, index) => (
        <span key={index} className={index < value ? styles.on : styles.off} />
      ))}
    </span>
  );
}

export default function Skills() {
  const { skills, technologies } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <>
      <PageHeader eyebrow={t('pages.skillsEyebrow')} title={t('pages.skillsTitle')} description={t('pages.skillsDescription')} />

      {skills.length === 0 && technologies.length === 0 ? (
        <Section>
          <Container>
            <EmptyState icon={<Layers aria-hidden="true" size={22} />} title={t('pages.empty')} />
          </Container>
        </Section>
      ) : null}

      {skills.length ? (
        <Section>
          <Container size="wide" className={styles.categories}>
            {skills.map((category, index) => (
              <Reveal key={category.id} delay={index * 0.05}>
                <Card padded className={styles.category}>
                  <h2 className={styles.categoryTitle}>{category.name}</h2>
                  <ul className={styles.skills}>
                    {category.skills.map((skill) => (
                      <li key={skill.id} className={styles.skill}>
                        <div className={styles.skillHead}>
                          <h3 className={styles.skillName}>{skill.name}</h3>
                          {skill.proficiency ? <Level value={skill.proficiency} /> : null}
                        </div>
                        {skill.description ? <p className={styles.skillText}>{skill.description}</p> : null}
                        <TechList technologies={skill.technologies} max={6} />
                      </li>
                    ))}
                  </ul>
                </Card>
              </Reveal>
            ))}
          </Container>
        </Section>
      ) : null}

      {technologies.length ? (
        <Section alt aria-labelledby="technologies-title">
          <Container size="wide">
            <SectionHeading id="technologies-title" eyebrow={t('home.stackEyebrow')} title={t('pages.stack')} />
            <div className={styles.techGrid}>
              {technologies.map((category) => (
                <div key={category.id}>
                  <h3 className={styles.techCategory}>{category.name}</h3>
                  <ul className={styles.techList}>
                    {category.technologies.map((technology) => (
                      <li key={technology.id} className={styles.tech}>
                        {technology.icon ? (
                          <img src={technology.icon.url} alt="" width={24} height={24} loading="lazy" />
                        ) : (
                          <span
                            className={styles.techDot}
                            style={technology.brand_color ? { background: technology.brand_color } : undefined}
                            aria-hidden="true"
                          />
                        )}
                        <span className={styles.techName}>{technology.name}</span>
                        {technology.proficiency ? <Level value={PROFICIENCY_LEVEL[technology.proficiency]} max={4} /> : null}
                      </li>
                    ))}
                  </ul>
                </div>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
