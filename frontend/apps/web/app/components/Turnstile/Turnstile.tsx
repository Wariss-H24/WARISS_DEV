import { useEffect, useRef } from 'react';

declare global {
  interface Window {
    turnstile?: {
      render: (element: HTMLElement, options: Record<string, unknown>) => string;
      reset: (widgetId?: string) => void;
      remove: (widgetId: string) => void;
    };
  }
}

const SITE_KEY = import.meta.env.VITE_TURNSTILE_SITE_KEY ?? '';
const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

let scriptPromise: Promise<void> | null = null;

function loadScript(): Promise<void> {
  scriptPromise ??= new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = SCRIPT_URL;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('turnstile'));
    document.head.appendChild(script);
  });

  return scriptPromise;
}

/**
 * Vérification anti-robot Cloudflare Turnstile (contrôlée ensuite par le serveur). Sans clé publique
 * configurée (développement local), rien n'est affiché et le serveur n'exige pas de jeton.
 */
export function Turnstile({ onToken, locale, resetKey = 0 }: { onToken: (token: string | null) => void; locale: string; resetKey?: number }) {
  const container = useRef<HTMLDivElement>(null);
  const widgetId = useRef<string | null>(null);

  useEffect(() => {
    if (!SITE_KEY || !container.current) {
      return;
    }

    let cancelled = false;
    loadScript()
      .then(() => {
        if (cancelled || !container.current || !window.turnstile) {
          return;
        }

        widgetId.current = window.turnstile.render(container.current, {
          sitekey: SITE_KEY,
          language: locale,
          theme: document.documentElement.dataset.theme === 'light' ? 'light' : 'dark',
          callback: (token: string) => onToken(token),
          'expired-callback': () => onToken(null),
          'error-callback': () => onToken(null),
        });
      })
      .catch(() => onToken(null));

    return () => {
      cancelled = true;

      if (widgetId.current && window.turnstile) {
        window.turnstile.remove(widgetId.current);
        widgetId.current = null;
      }
    };
    // Le widget est créé une fois ; resetKey le réinitialise après un envoi.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [locale, resetKey]);

  return SITE_KEY ? <div ref={container} /> : null;
}
