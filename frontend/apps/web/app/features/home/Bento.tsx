import type { Post, Profile } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Button, Card, Container, Section, SectionHeading } from '@wariss/ui';
import { ArrowRight, Check, Copy, Globe, Mail, MapPin } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { Reveal } from '~/components/Reveal/Reveal';
import { formatDate } from '~/lib/format';
import { contentPath, pathTo } from '~/lib/routes';
import styles from './Bento.module.css';

interface BentoProps {
  locale: Locale;
  profile: Profile | null;
  stats: { projects: number; technologies: number; posts: number };
  latestPost: Post | null;
}

function CopyEmail({ email }: { email: string }) {
  const { t } = useTranslation();
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(email);
      setCopied(true);
      window.setTimeout(() => setCopied(false), 2000);
    } catch {
      window.location.href = `mailto:${email}`;
    }
  };

  return (
    <Button variant="secondary" size="sm" onClick={copy} aria-live="polite">
      {copied ? <Check aria-hidden="true" /> : <Copy aria-hidden="true" />}
      {copied ? t('home.emailCopied') : t('home.copyEmail')}
    </Button>
  );
}

/** Grille « bento » de l'accueil : présentation, chiffres clés, localisation, dernier article, contact. */
export function Bento({ locale, profile, stats, latestPost }: BentoProps) {
  const { t } = useTranslation();
  const languages = profile?.spoken_languages ?? [];
  const languageNames = new Intl.DisplayNames([locale], { type: 'language' });

  return (
    <Section aria-labelledby="bento-title">
      <Container size="wide">
        <SectionHeading id="bento-title" eyebrow={t('home.bentoEyebrow')} title={t('home.bentoTitle')} />
        <div className={styles.grid}>
          <Reveal className={styles.about}>
            <Card spotlight padded className={styles.cell}>
              {profile?.avatar ? (
                <img src={profile.avatar.url} alt={profile.avatar.alt ?? profile.display_name} className={styles.avatar} width={72} height={72} />
              ) : null}
              <p className={styles.bio}>{profile?.short_bio ?? t('home.heroText')}</p>
              <Link to={pathTo(locale, 'about')} className={styles.more} prefetch="intent">
                {t('nav.about')}
                <ArrowRight aria-hidden="true" size={16} />
              </Link>
            </Card>
          </Reveal>

          <Reveal delay={0.05} className={styles.stats}>
            <Card padded className={styles.cell}>
              <dl className={styles.statList}>
                {(
                  [
                    [stats.projects, t('home.statsProjects')],
                    [stats.technologies, t('home.statsTechnologies')],
                    [stats.posts, t('home.statsArticles')],
                  ] as const
                ).map(([value, label]) => (
                  <div key={label} className={styles.stat}>
                    <dt className={styles.statLabel}>{label}</dt>
                    <dd className={styles.statValue}>{value}</dd>
                  </div>
                ))}
              </dl>
            </Card>
          </Reveal>

          <Reveal delay={0.1} className={styles.place}>
            <Card padded className={styles.cell}>
              {profile?.location_label ? (
                <p className={styles.line}>
                  <MapPin aria-hidden="true" size={18} />
                  <span>
                    <span className="mono-label">{t('home.basedIn')}</span>
                    <br />
                    {profile.location_label}
                  </span>
                </p>
              ) : null}
              {languages.length ? (
                <p className={styles.line}>
                  <Globe aria-hidden="true" size={18} />
                  <span>
                    <span className="mono-label">{t('home.speaks')}</span>
                    <br />
                    {languages.map((language) => languageNames.of(language.code) ?? language.code).join(' · ')}
                  </span>
                </p>
              ) : null}
            </Card>
          </Reveal>

          {latestPost ? (
            <Reveal delay={0.15} className={styles.post}>
              <Card interactive spotlight padded className={styles.cell}>
                <span className="mono-label">{t('home.latestPost')}</span>
                <h3 className={styles.postTitle}>
                  <Link to={contentPath(locale, 'blog', latestPost.slug)} className={styles.stretched} prefetch="intent">
                    {latestPost.title}
                  </Link>
                </h3>
                <p className={styles.postMeta}>{formatDate(latestPost.published_at, locale)}</p>
              </Card>
            </Reveal>
          ) : null}

          <Reveal delay={0.2} className={styles.contact}>
            <Card padded className={`${styles.cell} ${styles.contactCell}`}>
              <Mail aria-hidden="true" size={22} className={styles.contactIcon} />
              <p className={styles.contactTitle}>{t('home.ctaTitle')}</p>
              <div className={styles.contactActions}>
                <Button asChild size="sm">
                  <Link to={pathTo(locale, 'contact')}>{t('actions.contactMe')}</Link>
                </Button>
                {profile?.public_email ? <CopyEmail email={profile.public_email} /> : null}
              </div>
            </Card>
          </Reveal>
        </div>
      </Container>
    </Section>
  );
}
