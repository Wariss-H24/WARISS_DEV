import type { Locale } from '@wariss/i18n';
import { AuroraBackground, Button, Container } from '@wariss/ui';
import { ArrowRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { Reveal } from '~/components/Reveal/Reveal';
import { pathTo } from '~/lib/routes';
import styles from './CtaBanner.module.css';

/** Bandeau final d'appel au contact ; titre, texte et lien modifiables par une section « cta ». */
export function CtaBanner({
  locale,
  title,
  text,
  label,
  href,
}: {
  locale: Locale;
  title?: string | null;
  text?: string | null;
  label?: string | null;
  href?: string | null;
}) {
  const { t } = useTranslation();
  const target = href ?? pathTo(locale, 'contact');
  const internal = target.startsWith('/');

  return (
    <section className={styles.section} aria-labelledby="cta-title">
      <Container size="wide">
        <Reveal className={styles.banner}>
          <AuroraBackground grid={false} />
          <h2 id="cta-title" className={styles.title}>
            {title ?? t('home.ctaTitle')}
          </h2>
          <p className={styles.text}>{text ?? t('home.ctaText')}</p>
          <Button asChild variant="gradient" size="lg">
            {internal ? (
              <Link to={target}>
                {label ?? t('actions.contactMe')}
                <ArrowRight aria-hidden="true" />
              </Link>
            ) : (
              <a href={target} rel="noopener noreferrer">
                {label ?? t('actions.contactMe')}
                <ArrowRight aria-hidden="true" />
              </a>
            )}
          </Button>
        </Reveal>
      </Container>
    </section>
  );
}
