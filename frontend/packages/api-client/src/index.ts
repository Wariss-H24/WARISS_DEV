/**
 * Client de l'API WARISS.DEV.
 *
 * - `schema.d.ts` : types générés depuis backend/openapi.json (`pnpm api:types`), jamais édités.
 * - `public.ts` : formes de l'API publique (exception D-20, voir le fichier).
 * - `createApi` : client typé fin (openapi-fetch) pour les routes typées par le contrat.
 */
import createClient, { type ClientOptions } from 'openapi-fetch';
import type { paths } from './schema';

export type { components, operations, paths } from './schema';
export * from './public';

export function createApi(options: ClientOptions) {
  return createClient<paths>(options);
}

export type Api = ReturnType<typeof createApi>;
