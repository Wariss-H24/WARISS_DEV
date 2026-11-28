/**
 * Accès à l'API publique depuis le serveur du site (loaders). Les erreurs de l'API deviennent des
 * réponses HTTP : 404 si le contenu n'est pas publié, 503 si l'API est injoignable.
 */
import type { Locale } from '@wariss/i18n';
import { data } from 'react-router';

const API_URL = (process.env.API_URL ?? 'http://localhost:8000/api/v1').replace(/\/$/, '');

export const SITE_URL = (process.env.SITE_URL ?? 'http://localhost:5173').replace(/\/$/, '');

type Query = Record<string, string | number | boolean | null | undefined | string[]>;

function buildUrl(path: string, locale: Locale, query: Query = {}): string {
  const url = new URL(`${API_URL}/public${path}`);
  url.searchParams.set('locale', locale);

  for (const [key, value] of Object.entries(query)) {
    if (value === null || value === undefined || value === '' || value === false) {
      continue;
    }

    if (Array.isArray(value)) {
      value.forEach((item) => url.searchParams.append(`${key}[]`, item));
    } else {
      url.searchParams.set(key, String(value));
    }
  }

  return url.toString();
}

/**
 * GET sur l'API publique. Lève une réponse 404 / 503 exploitable par l'ErrorBoundary.
 */
export async function apiGet<T>(path: string, locale: Locale, query?: Query): Promise<T> {
  let response: Response;

  try {
    response = await fetch(buildUrl(path, locale, query), {
      headers: { Accept: 'application/json', 'Accept-Language': locale },
    });
  } catch {
    throw data({ reason: 'unavailable' }, { status: 503 });
  }

  if (response.status === 404) {
    throw data({ reason: 'not-found' }, { status: 404 });
  }

  if (!response.ok) {
    throw data({ reason: 'api-error' }, { status: response.status >= 500 ? 503 : response.status });
  }

  return (await response.json()) as T;
}

/**
 * Renvoie null si le contenu n'existe pas (404) ; les autres erreurs (API indisponible) remontent.
 */
export async function apiFind<T>(path: string, locale: Locale, query?: Query): Promise<T | null> {
  let response: Response;

  try {
    response = await fetch(buildUrl(path, locale, query), {
      headers: { Accept: 'application/json', 'Accept-Language': locale },
    });
  } catch {
    throw data({ reason: 'unavailable' }, { status: 503 });
  }

  if (response.status === 404) {
    return null;
  }

  if (!response.ok) {
    throw data({ reason: 'api-error' }, { status: 503 });
  }

  return (await response.json()) as T;
}

/**
 * Variante tolérante : renvoie `fallback` si l'appel échoue (bloc secondaire d'une page).
 */
export async function apiGetOr<T>(path: string, locale: Locale, fallback: T, query?: Query): Promise<T> {
  try {
    return await apiGet<T>(path, locale, query);
  } catch {
    return fallback;
  }
}

/** Requête brute (sitemap, téléchargement) : la réponse de l'API est relayée telle quelle. */
export function apiFetch(path: string, init?: RequestInit): Promise<Response> {
  return fetch(`${API_URL}/public${path}`, init);
}
