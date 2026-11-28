import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';
import '@fontsource-variable/jetbrains-mono';
import '@wariss/ui/styles.css';
import './styles/app.css';

import { createI18n, DEFAULT_LOCALE, isLocale } from '@wariss/i18n';
import { isRouteErrorResponse, Links, Meta, Outlet, Scripts, ScrollRestoration, useRouteLoaderData } from 'react-router';
import type { ReactNode } from 'react';
import type { Route } from './+types/root';
import { ErrorPage } from './components/ErrorPage/ErrorPage';
import { themeFromRequest } from './lib/preferences';

export function loader({ request }: Route.LoaderArgs) {
  const segment = new URL(request.url).pathname.split('/')[1];

  return {
    theme: themeFromRequest(request),
    locale: isLocale(segment) ? segment : DEFAULT_LOCALE,
    /** Adresse publique du site (URL canoniques, hreflang, Open Graph), lue côté serveur. */
    siteUrl: (process.env.SITE_URL ?? new URL(request.url).origin).replace(/\/$/, ''),
  };
}

export const links: Route.LinksFunction = () => [
  { rel: 'icon', href: '/favicon.svg', type: 'image/svg+xml' },
  { rel: 'manifest', href: '/site.webmanifest' },
];

export function Layout({ children }: { children: ReactNode }) {
  const data = useRouteLoaderData<typeof loader>('root');
  const theme = data?.theme ?? 'dark';

  return (
    <html lang={data?.locale ?? DEFAULT_LOCALE} data-theme={theme} suppressHydrationWarning>
      <head>
        <meta charSet="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
        <meta name="theme-color" content={theme === 'light' ? '#f5f7fb' : '#070b16'} />
        <meta name="color-scheme" content="dark light" />
        <Meta />
        <Links />
      </head>
      <body>
        {children}
        <ScrollRestoration />
        <Scripts />
      </body>
    </html>
  );
}

export default function App() {
  return <Outlet />;
}

/**
 * Erreur hors de la mise en page d'une langue (route inconnue à la racine, API indisponible…).
 */
export function ErrorBoundary({ error }: Route.ErrorBoundaryProps) {
  const data = useRouteLoaderData<typeof loader>('root');
  const i18n = createI18n(data?.locale ?? DEFAULT_LOCALE);
  const status = isRouteErrorResponse(error) ? error.status : 500;

  if (import.meta.env.DEV && !isRouteErrorResponse(error)) {
    console.error(error);
  }

  return <ErrorPage status={status} t={i18n.t} locale={data?.locale ?? DEFAULT_LOCALE} />;
}
