import { useCallback, type HTMLAttributes, type PointerEvent, type ReactNode } from 'react';
import { cx } from '../../lib/cx';
import styles from './Display.module.css';

export type BadgeTone = 'neutral' | 'accent' | 'highlight' | 'success' | 'warning' | 'danger';

export interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
  tone?: BadgeTone;
  /** Police mono (étiquettes techniques). */
  mono?: boolean;
  /** Pastille de statut devant le texte ; `pulse` l'anime (disponibilité). */
  dot?: boolean | 'pulse';
}

/** Pastille d'information : statut, catégorie, technologie. */
export function Badge({ tone = 'neutral', mono = false, dot = false, className, ...props }: BadgeProps) {
  return (
    <span
      className={cx(styles.badge, tone !== 'neutral' && styles[tone], mono && styles.mono, dot && styles.dot, dot === 'pulse' && styles.pulse, className)}
      {...props}
    />
  );
}

/** Touche de clavier (raccourcis). */
export function Kbd({ children, className }: { children: ReactNode; className?: string }) {
  return <kbd className={cx(styles.kbd, className)}>{children}</kbd>;
}

/** Bloc de chargement animé ; masqué des lecteurs d'écran. */
export function Skeleton({ width, height = '1rem', radius, className }: { width?: string; height?: string; radius?: string; className?: string }) {
  return <span aria-hidden="true" className={cx(styles.skeleton, className)} style={{ width, height, borderRadius: radius }} />;
}

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
  /** Effet au survol (carte cliquable). */
  interactive?: boolean;
  /** Halo et bordure lumineuse qui suivent le pointeur. */
  spotlight?: boolean;
  padded?: boolean;
}

/**
 * Carte du design system. Avec `spotlight`, la position du pointeur est transmise au CSS
 * (--pointer-x / --pointer-y) : l'effet reste entièrement en CSS, sans rendu React supplémentaire.
 */
export function Card({ interactive = false, spotlight = false, padded = false, className, onPointerMove, ...props }: CardProps) {
  const handlePointerMove = useCallback(
    (event: PointerEvent<HTMLDivElement>) => {
      if (spotlight) {
        const rect = event.currentTarget.getBoundingClientRect();
        event.currentTarget.style.setProperty('--pointer-x', `${event.clientX - rect.left}px`);
        event.currentTarget.style.setProperty('--pointer-y', `${event.clientY - rect.top}px`);
      }
      onPointerMove?.(event);
    },
    [spotlight, onPointerMove],
  );

  return (
    <div
      className={cx(styles.card, interactive && styles.cardInteractive, spotlight && styles.spotlight, padded && styles.cardPadded, className)}
      onPointerMove={handlePointerMove}
      {...props}
    />
  );
}

/** Fond « aurore » : dégradés qui dérivent, grille fine, grain. Décoratif. */
export function AuroraBackground({ grid = true, className }: { grid?: boolean; className?: string }) {
  return (
    <div aria-hidden="true" className={cx(styles.aurora, className)}>
      <span className={styles.auroraBlob} />
      <span className={styles.auroraBlob} />
      {grid ? <span className={styles.auroraGrid} /> : null}
      <span className={styles.auroraNoise} />
    </div>
  );
}

export interface EmptyStateProps {
  icon?: ReactNode;
  title: ReactNode;
  description?: ReactNode;
  action?: ReactNode;
}

/** État vide d'une liste : explique et propose l'action suivante. */
export function EmptyState({ icon, title, description, action }: EmptyStateProps) {
  return (
    <div className={styles.empty}>
      {icon ? <span className={styles.emptyIcon}>{icon}</span> : null}
      <p className={styles.emptyTitle}>{title}</p>
      {description ? <p>{description}</p> : null}
      {action}
    </div>
  );
}
