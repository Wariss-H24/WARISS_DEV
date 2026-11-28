import type { Locale } from '@wariss/i18n';
import { AuroraBackground, Button, Container } from '@wariss/ui';
import type { TFunction } from 'i18next';
import { ArrowLeft, RotateCw } from 'lucide-react';
import styles from './ErrorPage.module.css';

interface ErrorPageProps {
  status: number;
  t: TFunction;
  locale: Locale;
  /** Affichée dans la mise en page du site (en-tête et pied présents). */
  embedded?: boolean;
}

/**
 * Page d'erreur : 404 (contenu absent ou non publié) ou erreur serveur. Les liens sont de simples
 * ancres : la page fonctionne même si le routeur n'a pas pu démarrer.
 */
export function ErrorPage({ status, t, locale, embedded = false }: ErrorPageProps) {
  const notFound = status === 404;

  return (
    <section className={styles.page} data-embedded={embedded || undefined} aria-labelledby="error-title">
      <AuroraBackground />
      <Container size="reading" className={styles.inner}>
        <p className={styles.code} aria-hidden="true">
          {notFound ? '404' : status >= 500 ? '503' : status}
        </p>
        <h1 id="error-title" className={styles.title}>
          {notFound ? t('errors.notFoundTitle') : t('errors.serverTitle')}
        </h1>
        <p className={styles.text}>{notFound ? t('errors.notFoundText') : t('errors.serverText')}</p>
        <div className={styles.actions}>
          <Button asChild>
            <a href={`/${locale}`}>
              <ArrowLeft aria-hidden="true" />
              {t('errors.goHome')}
            </a>
          </Button>
          {notFound ? null : (
            <Button variant="secondary" onClick={() => window.location.reload()}>
              <RotateCw aria-hidden="true" />
              {t('actions.retry')}
            </Button>
          )}
        </div>
      </Container>
    </section>
  );
}
