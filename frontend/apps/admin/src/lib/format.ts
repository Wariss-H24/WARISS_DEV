/**
 * Mise en forme (administration en français).
 */
const dateTime = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
const date = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeZone: 'UTC' });
const relative = new Intl.RelativeTimeFormat('fr-FR', { numeric: 'auto' });

export function formatDateTime(value: string | null | undefined): string {
  return value ? dateTime.format(new Date(value)) : '—';
}

export function formatDate(value: string | null | undefined): string {
  return value ? date.format(new Date(value.length === 10 ? `${value}T12:00:00Z` : value)) : '—';
}

/** « il y a 3 heures » */
export function formatRelative(value: string | null | undefined): string {
  if (!value) {
    return '—';
  }

  const seconds = Math.round((new Date(value).getTime() - Date.now()) / 1000);
  const units: Array<[Intl.RelativeTimeFormatUnit, number]> = [
    ['year', 31_536_000],
    ['month', 2_592_000],
    ['day', 86_400],
    ['hour', 3600],
    ['minute', 60],
  ];

  for (const [unit, size] of units) {
    if (Math.abs(seconds) >= size) {
      return relative.format(Math.round(seconds / size), unit);
    }
  }

  return 'à l’instant';
}

export function formatNumber(value: number): string {
  return new Intl.NumberFormat('fr-FR').format(value);
}

export function formatBytes(bytes: number | null | undefined): string {
  if (!bytes) {
    return '—';
  }

  const [value, unit] = bytes >= 1024 * 1024 ? [bytes / (1024 * 1024), 'megabyte'] : [bytes / 1024, 'kilobyte'];

  return new Intl.NumberFormat('fr-FR', { style: 'unit', unit, unitDisplay: 'short', maximumFractionDigits: 1 }).format(value);
}

/** Libellé lisible d'un contenu de l'administration (titre, nom… dans sa traduction française). */
export function labelOf(item: Record<string, unknown> | null | undefined): string {
  if (!item) {
    return '—';
  }

  const translations = item.translations as Record<string, Record<string, unknown>> | undefined;
  const fr = translations?.fr ?? translations?.en ?? {};

  for (const key of ['title', 'name', 'label', 'job_title', 'degree_title', 'headline', 'quote']) {
    const value = fr[key] ?? item[key];

    if (typeof value === 'string' && value.trim()) {
      return value.length > 90 ? `${value.slice(0, 90)}…` : value;
    }
  }

  for (const key of [
    'display_name',
    'subject',
    'author_name',
    'organization_name',
    'institution_name',
    'issuer_name',
    'original_name',
    'from_path',
    'platform',
    'email',
    'key',
    'slug',
  ]) {
    const value = item[key];

    if (typeof value === 'string' && value.trim()) {
      return value;
    }
  }

  return `#${String(item.id ?? '')}`;
}
