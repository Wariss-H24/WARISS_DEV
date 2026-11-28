import type { Category } from '@wariss/api-client';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import styles from './CategoryFilter.module.css';

interface CategoryFilterProps {
  categories: Category[];
  active?: string | undefined;
  /** Adresse d'une catégorie (null : toutes). */
  hrefFor: (slug: string | null) => string;
}

/** Filtre par catégorie sous forme de liens (partageables, indexables, sans JavaScript). */
export function CategoryFilter({ categories, active, hrefFor }: CategoryFilterProps) {
  const { t } = useTranslation();

  if (categories.length < 2) {
    return null;
  }

  return (
    <nav aria-label={t('pages.filterBy')} className={styles.filter}>
      <ul className={styles.list}>
        <li>
          <Link to={hrefFor(null)} className={styles.chip} aria-current={active ? undefined : 'page'} preventScrollReset>
            {t('pages.allCategories')}
          </Link>
        </li>
        {categories.map((category) => (
          <li key={category.id}>
            <Link to={hrefFor(category.slug)} className={styles.chip} aria-current={active === category.slug ? 'page' : undefined} preventScrollReset>
              {category.name}
            </Link>
          </li>
        ))}
      </ul>
    </nav>
  );
}
