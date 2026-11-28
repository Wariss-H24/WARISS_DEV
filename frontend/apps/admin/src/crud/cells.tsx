import { Badge } from '@wariss/ui';
import { Check, Minus } from 'lucide-react';
import type { ReactNode } from 'react';
import { formatDate, formatDateTime, formatRelative, labelOf } from '~/lib/format';
import { optionLabel } from './options';
import type { ColumnDef } from './types';

/** Valeur à un chemin pointé (« translations.fr.title »). */
export function valueAt(item: Record<string, unknown>, path: string): unknown {
  return path.split('.').reduce<unknown>((value, key) => (value && typeof value === 'object' ? (value as Record<string, unknown>)[key] : undefined), item);
}

const STATUS_TONES: Record<string, 'success' | 'warning' | 'danger' | 'accent' | 'neutral'> = {
  published: 'success',
  approved: 'success',
  subscribed: 'success',
  active: 'success',
  sent: 'success',
  replied: 'success',
  scheduled: 'accent',
  new: 'accent',
  in_progress: 'accent',
  sending: 'accent',
  pending: 'warning',
  draft: 'neutral',
  read: 'neutral',
  archived: 'neutral',
  unsubscribed: 'neutral',
  rejected: 'danger',
  spam: 'danger',
  failed: 'danger',
  bounced: 'danger',
};

export function StatusBadge({ value, label }: { value: string; label: string }) {
  return (
    <Badge tone={STATUS_TONES[value] ?? 'neutral'} dot>
      {label}
    </Badge>
  );
}

/** Rendu d'une cellule de tableau selon le format de la colonne. */
export function renderCell(item: Record<string, unknown>, column: ColumnDef): ReactNode {
  const value = valueAt(item, column.path ?? column.key);

  switch (column.format) {
    case 'label':
      return <span style={{ fontWeight: 600 }}>{typeof value === 'string' && value ? value : labelOf(item)}</span>;
    case 'status':
      return value ? <StatusBadge value={String(value)} label={optionLabel(column.options, value)} /> : '—';
    case 'badge':
      return value !== null && value !== undefined && value !== '' ? <Badge mono>{optionLabel(column.options, value)}</Badge> : '—';
    case 'boolean':
      return value ? <Check aria-label="Oui" size={16} color="var(--color-success)" /> : <Minus aria-label="Non" size={16} color="var(--color-text-subtle)" />;
    case 'date':
      return formatDate(value as string | null);
    case 'datetime':
      return formatDateTime(value as string | null);
    case 'relative':
      return (
        <time dateTime={String(value ?? '')} title={formatDateTime(value as string | null)}>
          {formatRelative(value as string | null)}
        </time>
      );
    default:
      return value === null || value === undefined || value === '' ? '—' : String(value);
  }
}
