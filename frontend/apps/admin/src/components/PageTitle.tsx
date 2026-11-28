import { ChevronRight } from 'lucide-react';
import { useEffect, type ReactNode } from 'react';
import { Link } from 'react-router';
import styles from './PageTitle.module.css';

interface PageTitleProps {
  title: string;
  description?: ReactNode;
  breadcrumbs?: Array<{ label: string; to?: string }>;
  actions?: ReactNode;
  badge?: ReactNode;
}

/** En-tête d'une page de l'administration : fil d'Ariane, titre (h1), actions ; titre du document. */
export function PageTitle({ title, description, breadcrumbs = [], actions, badge }: PageTitleProps) {
  useEffect(() => {
    document.title = `${title} · Administration WARISS.DEV`;
  }, [title]);

  return (
    <header className={styles.header}>
      {breadcrumbs.length ? (
        <nav aria-label="Fil d’Ariane">
          <ol className={styles.crumbs}>
            {breadcrumbs.map((crumb, index) => (
              <li key={`${crumb.label}-${index}`}>
                {crumb.to ? <Link to={crumb.to}>{crumb.label}</Link> : <span aria-current="page">{crumb.label}</span>}
                {index < breadcrumbs.length - 1 ? <ChevronRight aria-hidden="true" size={13} /> : null}
              </li>
            ))}
          </ol>
        </nav>
      ) : null}
      <div className={styles.row}>
        <div className={styles.text}>
          <h1 className={styles.title}>
            {title}
            {badge}
          </h1>
          {description ? <p className={styles.description}>{description}</p> : null}
        </div>
        {actions ? <div className={styles.actions}>{actions}</div> : null}
      </div>
    </header>
  );
}
