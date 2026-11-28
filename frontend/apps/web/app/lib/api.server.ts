/**
 * Accès à l'API publique depuis le serveur du site (loaders). Les erreurs de l'API deviennent des
 * réponses HTTP : 404 si le contenu n'est pas publié, 503 si l'API est injoignable.
 *
 * Cache mémoire des lectures publiques (architecture §3.7) : une même adresse d'API n'est appelée
 * qu'une fois par période (API_CACHE_TTL secondes, 60 par défaut, 0 pour désactiver) et les appels
 * simultanés identiques sont fusionnés. Les aperçus de brouillons ne sont jamais mis en cache.
 */
import type { Locale } from '@wariss/i18n';
import { data } from 'react-router';

const API_URL = (process.env.API_URL ?? 'http://localhost:8000/api/v1').replace(/\/$/, '');
const CACHE_TTL_MS = Math.max(0, Number(process.env.API_CACHE_TTL ?? '60')) * 1000;
const CACHE_MAX_ENTRIES = 500;
const NEVER_CACHED = ['/preview/', '/search'];

export const SITE_URL = (process.env.SITE_URL ?? 'http://localhost:5173').replace(/\/$/, '');

type Query = Record<string, string | number | boolean | null | undefined | string[]>;

interface ApiResult {
  status: number;
  body: unknown;
}

const cache = new Map<string, { expiresAt: number; result: Promise<ApiResult> }>();

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

async function fetchResult(url: string, locale: Locale): Promise<ApiResult> {
  let response: Response;

  try {
    response = await fetch(url, { headers: { Accept: 'application/json', 'Accept-Language': locale } });
  } catch {
    throw data({ reason: 'unavailable' }, { status: 503 });
  }

  return { status: response.status, body: response.ok ? await response.json() : null };
}

/** Réponse de l'API, depuis le cache si possible (seuls les succès et les 404 sont gardés). */
function request(path: string, locale: Locale, query?: Query): Promise<ApiResult> {
  const url = buildUrl(path, locale, query);

  if (CACHE_TTL_MS === 0 || NEVER_CACHED.some((prefix) => path.startsWith(prefix))) {
    return fetchResult(url, locale);
  }

  const now = Date.now();
  const cached = cache.get(url);

  if (cached && cached.expiresAt > now) {
    return cached.result;
  }

  const result = fetchResult(url, locale);
  cache.set(url, { expiresAt: now + CACHE_TTL_MS, result });

  if (cache.size > CACHE_MAX_ENTRIES) {
    cache.delete(cache.keys().next().value as string);
  }

  // Une erreur (API indisponible, 5xx) ne doit pas rester en cache.
  result.then(
    ({ status }) => {
      if (status !== 200 && status !== 404) {
        cache.delete(url);
      }
    },
    () => cache.delete(url),
  );

  return result;
}

/**
 * GET sur l'API publique. Lève une réponse 404 / 503 exploitable par l'ErrorBoundary.
 */
export async function apiGet<T>(path: string, locale: Locale, query?: Query): Promise<T> {
  const { status, body } = await request(path, locale, query);

  if (status === 404) {
    throw data({ reason: 'not-found' }, { status: 404 });
  }

  if (status !== 200) {
    throw data({ reason: 'api-error' }, { status: status >= 500 ? 503 : status });
  }

  return body as T;
}

/**
 * Renvoie null si le contenu n'existe pas (404) ; les autres erreurs (API indisponible) remontent.
 */
export async function apiFind<T>(path: string, locale: Locale, query?: Query): Promise<T | null> {
  const { status, body } = await request(path, locale, query);

  if (status === 404) {
    return null;
  }

  if (status !== 200) {
    throw data({ reason: 'api-error' }, { status: 503 });
  }

  return body as T;
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
