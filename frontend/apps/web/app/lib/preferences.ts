/**
 * Préférences du visiteur stockées en cookie (lues côté serveur : aucun flash de thème ni de langue).
 */
import { DEFAULT_LOCALE, isLocale, LOCALES, type Locale } from '@wariss/i18n';

export type Theme = 'dark' | 'light';

export const THEME_COOKIE = 'theme';
export const LOCALE_COOKIE = 'lang';
const ONE_YEAR = 60 * 60 * 24 * 365;

export function readCookie(header: string | null, name: string): string | null {
  if (!header) {
    return null;
  }

  for (const part of header.split(';')) {
    const [key, ...rest] = part.trim().split('=');

    if (key === name) {
      return decodeURIComponent(rest.join('='));
    }
  }

  return null;
}

export function themeFromRequest(request: Request): Theme {
  return readCookie(request.headers.get('Cookie'), THEME_COOKIE) === 'light' ? 'light' : 'dark';
}

/**
 * Langue préférée : cookie, puis Accept-Language, puis français.
 */
export function preferredLocale(request: Request): Locale {
  const fromCookie = readCookie(request.headers.get('Cookie'), LOCALE_COOKIE);

  if (isLocale(fromCookie)) {
    return fromCookie;
  }

  const accepted = (request.headers.get('Accept-Language') ?? '').split(',').map((part) => part.split(';')[0]?.trim().slice(0, 2).toLowerCase());

  return accepted.find((code): code is Locale => isLocale(code)) ?? DEFAULT_LOCALE;
}

/** Écrit une préférence depuis le navigateur. */
export function writePreference(name: string, value: string): void {
  document.cookie = `${name}=${encodeURIComponent(value)}; Path=/; Max-Age=${ONE_YEAR}; SameSite=Lax`;
}

export function otherLocale(locale: Locale): Locale {
  return LOCALES.find((code) => code !== locale) ?? DEFAULT_LOCALE;
}
