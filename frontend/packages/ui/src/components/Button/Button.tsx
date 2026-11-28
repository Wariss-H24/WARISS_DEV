import { Slot } from 'radix-ui';
import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { cx } from '../../lib/cx';
import styles from './Button.module.css';

export type ButtonVariant = 'primary' | 'gradient' | 'secondary' | 'ghost' | 'danger';
export type ButtonSize = 'sm' | 'md' | 'lg';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  /** Bouton carré ne contenant qu'une icône : un `aria-label` est alors obligatoire. */
  iconOnly?: boolean;
  /** Occupe toute la largeur disponible. */
  block?: boolean;
  /** Affiche un indicateur de chargement et désactive le bouton. */
  loading?: boolean;
  /** Rend l'enfant (un lien, par exemple) avec l'apparence d'un bouton. */
  asChild?: boolean;
  children?: ReactNode;
}

/**
 * Bouton du design system. Avec `asChild`, il habille un lien (`<Link>`, `<a>`) sans ajouter
 * de balise, ce qui garde la sémantique correcte (un lien navigue, un bouton agit).
 */
export function Button({
  variant = 'primary',
  size = 'md',
  iconOnly = false,
  block = false,
  loading = false,
  asChild = false,
  className,
  children,
  disabled,
  type,
  ...props
}: ButtonProps) {
  const classes = cx(styles.button, styles[variant], size !== 'md' && styles[size], iconOnly && styles.iconOnly, block && styles.block, className);

  if (asChild) {
    return (
      <Slot.Root className={classes} {...props}>
        {children}
      </Slot.Root>
    );
  }

  return (
    <button type={type ?? 'button'} className={classes} disabled={disabled || loading} aria-busy={loading || undefined} {...props}>
      {loading ? <span className={styles.spinner} aria-hidden="true" /> : null}
      {children}
    </button>
  );
}
