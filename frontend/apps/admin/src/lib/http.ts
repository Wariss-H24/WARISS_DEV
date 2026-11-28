/**
 * Client HTTP de l'administration (architecture §4.2 : Axios côté admin).
 *
 * Authentification Sanctum en mode SPA : cookie de session + jeton CSRF (XSRF-TOKEN) envoyé
 * automatiquement. Un jeton expiré (419) est renouvelé puis la requête rejouée une fois ;
 * une session expirée (401) prévient l'application, qui renvoie vers la connexion.
 */
import axios, { AxiosError, type AxiosRequestConfig } from 'axios';

export const API_ORIGIN = (import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000').replace(/\/$/, '');
export const SITE_URL = (import.meta.env.VITE_SITE_URL ?? 'http://localhost:5173').replace(/\/$/, '');
export const SESSION_EXPIRED_EVENT = 'admin:session-expired';

export const http = axios.create({
  baseURL: `${API_ORIGIN}/api/v1`,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept-Language': 'fr' },
});

export function ensureCsrfCookie(): Promise<unknown> {
  return axios.get(`${API_ORIGIN}/sanctum/csrf-cookie`, { withCredentials: true });
}

http.interceptors.response.use(undefined, async (error: AxiosError) => {
  const config = error.config as (AxiosRequestConfig & { _retried?: boolean }) | undefined;

  if (error.response?.status === 419 && config && !config._retried) {
    config._retried = true;
    await ensureCsrfCookie();

    return http.request(config);
  }

  if (error.response?.status === 401 && !config?.url?.includes('/auth/')) {
    window.dispatchEvent(new Event(SESSION_EXPIRED_EVENT));
  }

  return Promise.reject(error);
});

/** Erreurs de validation (422) à plat : « translations.fr.title » → premier message. */
export function validationErrors(error: unknown): Record<string, string> | null {
  if (!(error instanceof AxiosError) || error.response?.status !== 422) {
    return null;
  }

  const errors = (error.response.data as { errors?: Record<string, string[]> }).errors ?? {};

  return Object.fromEntries(Object.entries(errors).map(([field, messages]) => [field, messages[0] ?? '']));
}

/** Message lisible d'une erreur d'API (message du serveur, sinon un message générique). */
export function errorMessage(error: unknown): string {
  if (error instanceof AxiosError) {
    const message = (error.response?.data as { message?: string } | undefined)?.message;

    if (error.response?.status === 403) {
      return message || 'Ce compte est en lecture seule.';
    }

    if (!error.response) {
      return 'Impossible de joindre l’API. Vérifiez qu’elle est démarrée.';
    }

    return message || `Erreur ${error.response.status}.`;
  }

  return 'Une erreur inattendue est survenue.';
}
