import { Button, Container, EmptyState, Section } from '@wariss/ui';
import { SearchX } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Pagination } from '~/components/Pagination/Pagination';
import { CardGrid } from './CardGrid';

interface ListingPageProps {
  eyebrow: string;
  title: ReactNode;
  description?: string | null;
  filter?: ReactNode;
  cards: ReactNode[];
  meta: { current_page: number; last_page: number };
  /** Lien pour retirer le filtre courant (état vide filtré). */
  clearHref?: string | null;
  columns?: 2 | 3;
}

/** Gabarit des pages de liste : en-tête, filtre, grille de cartes, pagination, état vide. */
export function ListingPage({ eyebrow, title, description, filter, cards, meta, clearHref, columns = 3 }: ListingPageProps) {
  const { t } = useTranslation();

  return (
    <>
      <PageHeader eyebrow={eyebrow} title={title} description={description} />
      <Section>
        <Container size="wide">
          {filter}
          {cards.length ? (
            <CardGrid columns={columns}>{cards}</CardGrid>
          ) : (
            <EmptyState
              icon={<SearchX aria-hidden="true" size={22} />}
              title={clearHref ? t('pages.emptyFiltered') : t('pages.empty')}
              action={
                clearHref ? (
                  <Button asChild variant="secondary" size="sm">
                    <Link to={clearHref}>{t('pages.clearFilter')}</Link>
                  </Button>
                ) : null
              }
            />
          )}
          <Pagination current={meta.current_page} last={meta.last_page} />
        </Container>
      </Section>
    </>
  );
}
