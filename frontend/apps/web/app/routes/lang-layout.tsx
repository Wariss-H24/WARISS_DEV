import type { Site } from '@wariss/api-client';
import { createI18n, isLocale, type Locale } from '@wariss/i18n';
import { TooltipProvider } from '@wariss/ui';
import { useEffect, useMemo } from 'react';
import { I18nextProvider } from 'react-i18next';
import { data, isRouteErrorResponse, Outlet, useLoaderData, useRouteLoaderData, type ShouldRevalidateFunctionArgs } from 'react-router';
import type { Route } from './+types/lang-layout';
import { CommandPalette } from '~/components/CommandPalette/CommandPalette';
import { ErrorPage } from '~/components/ErrorPage/ErrorPage';
import { SiteFooter } from '~/components/SiteFooter/SiteFooter';
import { SiteHeader } from '~/components/SiteHeader/SiteHeader';
import { apiGet } from '~/lib/api.server';
import { LOCALE_COOKIE, writePreference } from '~/lib/preferences';
import { usePageViewTracking } from '~/lib/tracking';

export async function loader({ params }: Route.LoaderArgs) {
  if (!isLocale(params.lang)) {
    throw data({ reason: 'not-found' }, { status: 404 });
  }

  const locale: Locale = params.lang;

  try {
    const { data: site } = await apiGet<{ data: Site }>('/site', locale);

    return { locale, site };
  } catch {
    // L'API est injoignable : la mise en page reste affichée, chaque page gère son propre contenu.
    return { locale, site: null as Site | null };
  }
}

export function shouldRevalidate({ currentParams, nextParams }: ShouldRevalidateFunctionArgs) {
  // Les données communes (menus, profil, réglages) ne changent qu'avec la langue.
  return currentParams.lang !== nextParams.lang;
}

function Shell({ locale, site, children }: { locale: Locale; site: Site | null; children: React.ReactNode }) {
  const i18n = useMemo(() => createI18n(locale), [locale]);

  useEffect(() => {
    writePreference(LOCALE_COOKIE, locale);
  }, [locale]);

  return (
    <I18nextProvider i18n={i18n}>
      <TooltipProvider delayDuration={250}>
        <SiteHeader locale={locale} site={site} />
        <main id="content" tabIndex={-1}>
          {children}
        </main>
        <SiteFooter locale={locale} site={site} />
        <CommandPalette locale={locale} site={site} />
      </TooltipProvider>
    </I18nextProvider>
  );
}

export default function LangLayout() {
  const { locale, site } = useLoaderData<typeof loader>();
  usePageViewTracking(locale);

  return (
    <Shell locale={locale} site={site}>
      <Outlet />
    </Shell>
  );
}

/** Erreur d'une page (404, API indisponible) : affichée dans la mise en page du site. */
export function ErrorBoundary({ error, params }: Route.ErrorBoundaryProps) {
  const layout = useRouteLoaderData<typeof loader>('routes/lang-layout');
  const locale: Locale = isLocale(params.lang) ? params.lang : 'fr';
  const i18n = useMemo(() => createI18n(locale), [locale]);
  const status = isRouteErrorResponse(error) ? error.status : 500;

  if (!isLocale(params.lang)) {
    return <ErrorPage status={404} t={i18n.t} locale={locale} />;
  }

  return (
    <Shell locale={locale} site={layout?.site ?? null}>
      <ErrorPage status={status} t={i18n.t} locale={locale} embedded />
    </Shell>
  );
}
