import { AuroraBackground, Container } from '@wariss/ui';
import type { ReactNode } from 'react';
import styles from './PageHeader.module.css';

interface PageHeaderProps {
  eyebrow?: ReactNode;
  title: ReactNode;
  description?: ReactNode;
  /** Éléments sous la description : boutons, métadonnées, filtres. */
  children?: ReactNode;
  /** Visuel à droite sur grand écran (image de couverture, carte). */
  aside?: ReactNode;
}

/** En-tête des pages intérieures : fond aurore, sur-titre, titre (h1), description. */
export function PageHeader({ eyebrow, title, description, children, aside }: PageHeaderProps) {
  return (
    <header className={styles.header}>
      <AuroraBackground />
      <Container size="wide" className={aside ? styles.withAside : styles.inner}>
        <div className={styles.text}>
          {eyebrow ? <p className={styles.eyebrow}>{eyebrow}</p> : null}
          <h1 className={styles.title}>{title}</h1>
          {description ? <p className={styles.description}>{description}</p> : null}
          {children ? <div className={styles.extra}>{children}</div> : null}
        </div>
        {aside ? <div className={styles.aside}>{aside}</div> : null}
      </Container>
    </header>
  );
}
