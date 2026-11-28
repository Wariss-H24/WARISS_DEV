import { Button } from '@wariss/ui';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link, useLocation } from 'react-router';
import styles from './Pagination.module.css';

/** Pagination par liens (?page=), utilisable sans JavaScript et indexable. */
export function Pagination({ current, last }: { current: number; last: number }) {
  const { t } = useTranslation();
  const location = useLocation();

  if (last <= 1) {
    return null;
  }

  const hrefFor = (page: number) => {
    const params = new URLSearchParams(location.search);

    if (page === 1) {
      params.delete('page');
    } else {
      params.set('page', String(page));
    }

    const query = params.toString();

    return `${location.pathname}${query ? `?${query}` : ''}`;
  };

  return (
    <nav aria-label={t('pages.pagination')} className={styles.nav}>
      {current > 1 ? (
        <Button asChild variant="secondary" size="sm">
          <Link to={hrefFor(current - 1)} rel="prev" preventScrollReset={false}>
            <ChevronLeft aria-hidden="true" />
            {t('actions.previous')}
          </Link>
        </Button>
      ) : (
        <span />
      )}
      <p className={styles.status} aria-current="page">
        {t('pages.page', { current, total: last })}
      </p>
      {current < last ? (
        <Button asChild variant="secondary" size="sm">
          <Link to={hrefFor(current + 1)} rel="next">
            {t('actions.next')}
            <ChevronRight aria-hidden="true" />
          </Link>
        </Button>
      ) : (
        <span />
      )}
    </nav>
  );
}
