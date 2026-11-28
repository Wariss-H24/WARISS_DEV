import type { Route } from './+types/fallback';
import { resolveRedirectOr404 } from '~/lib/redirects.server';

/** Adresse inconnue hors langue : redirection enregistrée, sinon 404 (page d'erreur racine). */
export function loader({ request }: Route.LoaderArgs) {
  return resolveRedirectOr404(request);
}

export default function Fallback() {
  return null;
}
