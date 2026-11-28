/**
 * Mise en forme selon la langue (Intl) : dates, périodes, prix.
 */
import type { Locale } from '@wariss/i18n';

const LOCALE_TAGS: Record<Locale, string> = { fr: 'fr-FR', en: 'en-GB' };

export function formatDate(value: string | null | undefined, locale: Locale, style: 'long' | 'month' | 'year' = 'long'): string {
  if (!value) {
    return '';
  }

  const date = new Date(value.length === 10 ? `${value}T12:00:00Z` : value);
  const options: Intl.DateTimeFormatOptions =
    style === 'year' ? { year: 'numeric' } : style === 'month' ? { month: 'short', year: 'numeric' } : { day: 'numeric', month: 'long', year: 'numeric' };

  return new Intl.DateTimeFormat(LOCALE_TAGS[locale], { ...options, timeZone: 'UTC' }).format(date);
}

/** Période « janv. 2024 – aujourd'hui ». */
export function formatPeriod(start: string | null | undefined, end: string | null | undefined, locale: Locale, presentLabel: string): string {
  if (!start) {
    return end ? formatDate(end, locale, 'month') : '';
  }

  return `${formatDate(start, locale, 'month')} – ${end ? formatDate(end, locale, 'month') : presentLabel}`;
}

export function formatPrice(amount: string | null, currency: string | null, locale: Locale): string | null {
  if (!amount) {
    return null;
  }

  return new Intl.NumberFormat(LOCALE_TAGS[locale], {
    style: 'currency',
    currency: currency ?? 'EUR',
    maximumFractionDigits: 0,
  }).format(Number(amount));
}

export function localeTag(locale: Locale): string {
  return LOCALE_TAGS[locale];
}

/** Taille de fichier lisible dans la langue (Ko / kB, Mo / MB). */
export function formatSize(bytes: number, locale: Locale): string {
  const [value, unit] = bytes >= 1024 * 1024 ? [bytes / (1024 * 1024), 'megabyte'] : [Math.max(1, bytes / 1024), 'kilobyte'];

  return new Intl.NumberFormat(LOCALE_TAGS[locale], { style: 'unit', unit, unitDisplay: 'short', maximumFractionDigits: 1 }).format(value);
}
