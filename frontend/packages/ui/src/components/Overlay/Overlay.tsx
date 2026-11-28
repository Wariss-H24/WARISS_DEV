import { X } from 'lucide-react';
import { Dialog as RadixDialog, Tooltip as RadixTooltip } from 'radix-ui';
import type { ReactNode } from 'react';
import { cx } from '../../lib/cx';
import { Button } from '../Button/Button';
import styles from './Overlay.module.css';

export interface DialogProps {
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
  /** Élément qui ouvre la fenêtre (rendu tel quel). */
  trigger?: ReactNode;
  title: ReactNode;
  description?: ReactNode;
  /** Libellé accessible du bouton de fermeture. */
  closeLabel: string;
  /** Panneau latéral (menu mobile) au lieu d'une fenêtre centrée. */
  side?: boolean;
  /** Masque visuellement le titre (il reste annoncé aux lecteurs d'écran). */
  hideTitle?: boolean;
  className?: string;
  children?: ReactNode;
}

/**
 * Fenêtre modale accessible (Radix) : focus piégé, fermeture par Échap, titre annoncé.
 * Avec `side`, devient un panneau latéral (menu mobile).
 */
export function Dialog({ open, onOpenChange, trigger, title, description, closeLabel, side = false, hideTitle = false, className, children }: DialogProps) {
  return (
    <RadixDialog.Root open={open} onOpenChange={onOpenChange}>
      {trigger ? <RadixDialog.Trigger asChild>{trigger}</RadixDialog.Trigger> : null}
      <RadixDialog.Portal>
        <RadixDialog.Overlay className={styles.overlay} />
        <RadixDialog.Content className={cx(side ? styles.sheet : styles.content, className)}>
          <RadixDialog.Title className={cx(styles.title, hideTitle && 'visually-hidden')}>{title}</RadixDialog.Title>
          {description ? (
            <RadixDialog.Description className={styles.description}>{description}</RadixDialog.Description>
          ) : (
            <RadixDialog.Description className="visually-hidden">{title}</RadixDialog.Description>
          )}
          {children}
          <RadixDialog.Close asChild>
            <Button variant="ghost" size="sm" iconOnly aria-label={closeLabel} className={styles.close}>
              <X aria-hidden="true" />
            </Button>
          </RadixDialog.Close>
        </RadixDialog.Content>
      </RadixDialog.Portal>
    </RadixDialog.Root>
  );
}

export interface TooltipProps {
  content: ReactNode;
  children: ReactNode;
  side?: 'top' | 'right' | 'bottom' | 'left';
}

/** Infobulle accessible (au survol et au focus). Nécessite <TooltipProvider> à la racine. */
export function Tooltip({ content, children, side = 'top' }: TooltipProps) {
  return (
    <RadixTooltip.Root>
      <RadixTooltip.Trigger asChild>{children}</RadixTooltip.Trigger>
      <RadixTooltip.Portal>
        <RadixTooltip.Content side={side} sideOffset={6} className={styles.tooltip}>
          {content}
        </RadixTooltip.Content>
      </RadixTooltip.Portal>
    </RadixTooltip.Root>
  );
}

export const TooltipProvider = RadixTooltip.Provider;
