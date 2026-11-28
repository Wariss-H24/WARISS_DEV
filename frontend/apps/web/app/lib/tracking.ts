/**
 * Mesure d'audience sans cookie (D-16) depuis le navigateur : une vue par page affichée et les
 * événements d'interaction (partage, clic sortant…). L'API ignore elle-même robots, DNT et GPC ;
 * un échec n'a jamais d'effet sur la navigation.
 */
import type { PublicContentType, ShareNetwork } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { useEffect, useRef } from 'react';
import { useLocation, useMatches } from 'react-router';

export const BROWSER_API_URL = (import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1').replace(/\/$/, '');

/** Contenu affiché, déclaré par la route dans les données de son loader (`tracking`). */
export interface TrackedContent {
  type: PublicContentType;
  id: number;
}

function post(path: string, body: Record<string, unknown>): void {
  try {
    void fetch(`${BROWSER_API_URL}/public/track/${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
      keepalive: true,
      credentials: 'omit',
    }).catch(() => undefined);
  } catch {
    // Mesure facultative : jamais d'erreur visible.
  }
}

function trackedContent(matches: ReturnType<typeof useMatches>): TrackedContent | null {
  for (let index = matches.length - 1; index >= 0; index--) {
    const tracking = (matches[index]?.loaderData as { tracking?: TrackedContent } | undefined)?.tracking;

    if (tracking) {
      return tracking;
    }
  }

  return null;
}

/** Enregistre une vue à chaque page affichée (y compris la première, avec sa provenance). */
export function usePageViewTracking(locale: Locale): void {
  const location = useLocation();
  const matches = useMatches();
  const isFirstView = useRef(true);

  useEffect(() => {
    if (location.pathname.includes('/preview/')) {
      return;
    }

    const params = new URLSearchParams(location.search);
    const content = trackedContent(matches);

    post('view', {
      path: location.pathname,
      locale,
      referrer: isFirstView.current ? document.referrer || null : null,
      utm_source: params.get('utm_source'),
      utm_medium: params.get('utm_medium'),
      utm_campaign: params.get('utm_campaign'),
      content_type: content?.type ?? null,
      content_id: content?.id ?? null,
    });
    isFirstView.current = false;
    // Une vue par changement d'adresse ; les données des routes suivent l'adresse.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location.pathname, locale]);
}

export function trackShare(network: ShareNetwork, content?: TrackedContent | null): void {
  post('event', {
    name: 'share',
    network,
    path: window.location.pathname,
    content_type: content?.type ?? null,
    content_id: content?.id ?? null,
  });
}

export function trackEvent(name: 'cta_click' | 'demo_open' | 'outbound_click', options: { target?: string; content?: TrackedContent | null } = {}): void {
  post('event', {
    name,
    path: window.location.pathname,
    target: options.target ?? null,
    content_type: options.content?.type ?? null,
    content_id: options.content?.id ?? null,
  });
}

/** Téléchargement du CV : servi directement par l'API, qui compte l'événement cv_download. */
export function resumeDownloadUrl(locale: Locale): string {
  return `${BROWSER_API_URL}/public/resume/download?locale=${locale}`;
}
