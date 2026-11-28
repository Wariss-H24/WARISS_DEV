import { Button, Dialog } from '@wariss/ui';
import type { ReactNode } from 'react';

interface ConfirmDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  title: string;
  description?: ReactNode;
  confirmLabel: string;
  tone?: 'danger' | 'primary';
  loading?: boolean;
  onConfirm: () => void;
  children?: ReactNode;
}

/** Confirmation d'une action irréversible ou sensible (suppression, publication…). */
export function ConfirmDialog({ open, onOpenChange, title, description, confirmLabel, tone = 'danger', loading, onConfirm, children }: ConfirmDialogProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange} title={title} description={description} closeLabel="Annuler">
      {children}
      <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 'var(--space-2)', marginTop: 'var(--space-6)' }}>
        <Button variant="secondary" onClick={() => onOpenChange(false)}>
          Annuler
        </Button>
        <Button variant={tone === 'danger' ? 'danger' : 'primary'} loading={loading} onClick={onConfirm}>
          {confirmLabel}
        </Button>
      </div>
    </Dialog>
  );
}
