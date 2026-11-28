import type { Media, Technology } from '@wariss/api-client';
import { Badge, Card } from '@wariss/ui';
import { ArrowUpRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { Link } from 'react-router';
import { TechList } from '../TechList/TechList';
import styles from './ContentCard.module.css';

interface ContentCardProps {
  href: string;
  title: string;
  summary?: string | null;
  cover?: Media | null;
  eyebrow?: ReactNode;
  meta?: ReactNode;
  technologies?: Technology[] | undefined;
  featured?: boolean;
  /** Mise en page horizontale (liste d'articles). */
  layout?: 'vertical' | 'horizontal';
  /** Titre de niveau h3 (dans une section) ou h2 (page de liste). */
  headingLevel?: 'h2' | 'h3';
  /** Graine du visuel génératif quand il n'y a pas d'image. */
  seed?: number;
}

/** Visuel de repli : motif géométrique dérivé de l'identifiant, toujours le même pour un contenu. */
function GeneratedCover({ seed, title }: { seed: number; title: string }) {
  const angle = (seed * 47) % 360;
  const initials = title
    .split(/\s+/)
    .slice(0, 2)
    .map((word) => word[0])
    .join('')
    .toUpperCase();

  return (
    <div className={styles.generated} style={{ '--angle': `${angle}deg` } as React.CSSProperties} aria-hidden="true">
      <span className={styles.initials}>{initials}</span>
    </div>
  );
}

/**
 * Carte de contenu (projet, service, expérimentation, étude de cas, article) : toute la carte est
 * cliquable par un seul lien (le titre), ce qui évite des liens en double pour les lecteurs d'écran.
 */
export function ContentCard({
  href,
  title,
  summary,
  cover,
  eyebrow,
  meta,
  technologies,
  featured = false,
  layout = 'vertical',
  headingLevel = 'h3',
  seed = 1,
}: ContentCardProps) {
  const Heading = headingLevel;

  return (
    <Card interactive spotlight className={layout === 'horizontal' ? styles.horizontal : styles.card}>
      <div className={styles.media}>
        {cover ? (
          <img src={cover.url} alt="" width={cover.width ?? undefined} height={cover.height ?? undefined} loading="lazy" decoding="async" />
        ) : (
          <GeneratedCover seed={seed} title={title} />
        )}
        {featured ? (
          <Badge tone="accent" className={styles.featured}>
            ★
          </Badge>
        ) : null}
      </div>
      <div className={styles.body}>
        {eyebrow ? <div className={styles.eyebrow}>{eyebrow}</div> : null}
        <Heading className={styles.title}>
          <Link to={href} className={styles.link} prefetch="intent">
            {title}
          </Link>
          <ArrowUpRight aria-hidden="true" className={styles.arrow} size={18} />
        </Heading>
        {summary ? <p className={styles.summary}>{summary}</p> : null}
        <div className={styles.footer}>
          <TechList technologies={technologies} max={4} />
          {meta ? <div className={styles.meta}>{meta}</div> : null}
        </div>
      </div>
    </Card>
  );
}
