import type { Route } from './+types/not-found';
import { resolveRedirectOr404 } from '~/lib/redirects.server';

/** Adresse inconnue sous une langue : redirection enregistrée, sinon 404 dans la mise en page. */
export function loader({ request }: Route.LoaderArgs) {
  return resolveRedirectOr404(request);
}

export default function NotFound() {
  return null;
}
