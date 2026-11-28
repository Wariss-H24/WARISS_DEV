import type { Profile, Technology } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { AuroraBackground, Badge, Button, Container, GradientText } from '@wariss/ui';
import { ArrowRight, FileDown, MapPin } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { Reveal } from '~/components/Reveal/Reveal';
import { formatDate } from '~/lib/format';
import { pathTo } from '~/lib/routes';
import { resumeDownloadUrl } from '~/lib/tracking';
import styles from './Hero.module.css';

interface HeroProps {
  locale: Locale;
  profile: Profile | null;
  technologies: Technology[];
  /** Titre et texte d'une section « hero » saisie dans l'administration (prioritaires). */
  title?: string | null;
  text?: string | null;
}

/** Fenêtre de code qui présente le profil comme un objet : signature visuelle du site. */
function ProfileWindow({ profile, technologies }: { profile: Profile | null; technologies: Technology[] }) {
  const stack = technologies.slice(0, 4).map((technology) => technology.name);
  const lines: Array<[string, string, 'string' | 'array' | 'boolean']> = [
    ['name', profile?.display_name ?? 'Wariss', 'string'],
    ['role', profile?.headline ?? 'Full-stack developer', 'string'],
    ['location', profile?.location_label ?? '', 'string'],
    ['stack', stack.length ? JSON.stringify(stack) : '["Laravel", "React", "PostgreSQL"]', 'array'],
    ['available', String((profile?.availability_status ?? 'available') !== 'unavailable'), 'boolean'],
  ];

  return (
    <div className={styles.window} aria-hidden="true">
      <div className={styles.windowBar}>
        <span className={styles.dots}>
          <span />
          <span />
          <span />
        </span>
        <span className={styles.windowTitle}>~/wariss — whoami.ts</span>
      </div>
      <pre className={styles.code}>
        <code>
          <span className={styles.keyword}>const</span> <span className={styles.variable}>developer</span> = {'{'}
          {'\n'}
          {lines
            .filter(([, value]) => value !== '')
            .map(([key, value, type]) => (
              <span key={key}>
                {'  '}
                <span className={styles.property}>{key}</span>: <span className={styles[type]}>{type === 'string' ? `'${value}'` : value}</span>,{'\n'}
              </span>
            ))}
          {'}'};{'\n\n'}
          <span className={styles.keyword}>await</span> developer.<span className={styles.fn}>ship</span>(
          <span className={styles.string}>'your-next-product'</span>)<span className={styles.caret} />
        </code>
      </pre>
    </div>
  );
}

export function Hero({ locale, profile, technologies, title, text }: HeroProps) {
  const { t } = useTranslation();
  const status = profile?.availability_status ?? 'available';
  const tone = status === 'available' ? 'success' : status === 'limited' ? 'warning' : 'neutral';

  return (
    <section className={styles.hero} aria-labelledby="hero-title">
      <AuroraBackground />
      <Container size="wide" className={styles.grid}>
        <div className={styles.text}>
          <Reveal>
            <Badge tone={tone} dot="pulse" className={styles.status}>
              {t(`availability.${status}`)}
              {status !== 'available' && profile?.available_from ? ` · ${t('availability.from', { date: formatDate(profile.available_from, locale) })}` : ''}
            </Badge>
          </Reveal>
          <Reveal delay={0.05}>
            <h1 id="hero-title" className={styles.title}>
              {title ? (
                title
              ) : (
                <>
                  {t('home.heroTitle')} <GradientText>{t('home.heroHighlight')}</GradientText>
                </>
              )}
            </h1>
          </Reveal>
          <Reveal delay={0.1}>
            <p className={styles.lead}>{text ?? profile?.short_bio ?? t('home.heroText')}</p>
          </Reveal>
          <Reveal delay={0.15} className={styles.actions}>
            <Button asChild variant="gradient" size="lg">
              <Link to={pathTo(locale, 'projects')} prefetch="intent">
                {t('actions.viewProjects')}
                <ArrowRight aria-hidden="true" />
              </Link>
            </Button>
            <Button asChild variant="secondary" size="lg">
              <Link to={pathTo(locale, 'contact')} prefetch="intent">
                {t('actions.contactMe')}
              </Link>
            </Button>
            <Button asChild variant="ghost" size="lg">
              <a href={resumeDownloadUrl(locale)} download>
                <FileDown aria-hidden="true" />
                {t('actions.downloadResume')}
              </a>
            </Button>
          </Reveal>
          {profile?.location_label ? (
            <Reveal delay={0.2}>
              <p className={styles.location}>
                <MapPin aria-hidden="true" size={15} />
                {profile.location_label}
              </p>
            </Reveal>
          ) : null}
        </div>
        <Reveal delay={0.15} className={styles.visual}>
          <ProfileWindow profile={profile} technologies={technologies} />
        </Reveal>
      </Container>
    </section>
  );
}
