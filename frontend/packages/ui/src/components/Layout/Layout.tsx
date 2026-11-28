import type { ElementType, HTMLAttributes, ReactNode } from 'react';
import { cx } from '../../lib/cx';
import styles from './Layout.module.css';

export interface ContainerProps extends HTMLAttributes<HTMLElement> {
  size?: 'default' | 'wide' | 'reading';
  as?: ElementType;
}

/** Conteneur centré avec gouttières fluides. */
export function Container({ size = 'default', as: Tag = 'div', className, ...props }: ContainerProps) {
  return <Tag className={cx(styles.container, size !== 'default' && styles[size], className)} {...props} />;
}

export interface SectionProps extends HTMLAttributes<HTMLElement> {
  /** Fond alterné, pour rythmer une longue page. */
  alt?: boolean;
}

/** Section de page avec l'espacement vertical fluide du design system. */
export function Section({ alt = false, className, ...props }: SectionProps) {
  return <section className={cx(styles.section, alt && styles.sectionAlt, className)} {...props} />;
}

export interface SectionHeadingProps {
  eyebrow?: ReactNode;
  title: ReactNode;
  description?: ReactNode;
  align?: 'start' | 'center';
  /** Niveau du titre (h2 par défaut). */
  as?: 'h1' | 'h2' | 'h3';
  id?: string;
  className?: string;
}

/** Titre de section : sur-titre en mono, titre d'affichage, description. */
export function SectionHeading({ eyebrow, title, description, align = 'start', as: Heading = 'h2', id, className }: SectionHeadingProps) {
  return (
    <div className={cx(styles.heading, align === 'center' && styles.headingCenter, className)}>
      {eyebrow ? <span className={styles.eyebrow}>{eyebrow}</span> : null}
      <Heading id={id} className={styles.title}>
        {title}
      </Heading>
      {description ? <p className={styles.description}>{description}</p> : null}
    </div>
  );
}

/** Mot ou expression mis en valeur par le dégradé de marque (jamais un paragraphe). */
export function GradientText({ children, className }: { children: ReactNode; className?: string }) {
  return <span className={cx(styles.gradientText, className)}>{children}</span>;
}
