/**
 * Aides partagées par les routes du site.
 */
import type { Site } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';

/** Langue de la route (la mise en page a déjà refusé une langue inconnue). */
export function routeLocale(params: { lang?: string }): Locale {
  return params.lang === 'en' ? 'en' : 'fr';
}

interface MatchLike {
  id: string;
  loaderData?: unknown;
}

/** Données de la mise en page (site, langue) depuis les correspondances d'une fonction meta. */
export function layoutSite(matches: ReadonlyArray<MatchLike | undefined>): Site | null {
  const layout = matches.find((match) => match?.id === 'routes/lang-layout')?.loaderData as { site?: Site | null } | undefined;

  return layout?.site ?? null;
}

export function siteName(site: Site | null): string {
  return (site?.settings.general?.site_name as string | undefined) ?? 'WARISS.DEV';
}

/** Page demandée (?page=), bornée. */
export function pageParam(request: Request): number {
  const page = Number(new URL(request.url).searchParams.get('page') ?? '1');

  return Number.isInteger(page) && page > 0 && page < 1000 ? page : 1;
}

export function searchParam(request: Request, name: string): string | undefined {
  const value = new URL(request.url).searchParams.get(name);

  return value && /^[a-z0-9-]{1,100}$/.test(value) ? value : undefined;
}

/** Adresse publique du site (lue côté serveur dans le loader racine, disponible partout). */
export function siteUrlOf(matches: ReadonlyArray<MatchLike | undefined>): string {
  const root = matches.find((match) => match?.id === 'root')?.loaderData as { siteUrl?: string } | undefined;

  return root?.siteUrl ?? '';
}
