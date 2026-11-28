/**
 * Adresse introuvable : on demande à l'API si une redirection existe (D-06 : slugs modifiés,
 * redirections manuelles), avant de répondre 404.
 */
import { data, redirect } from 'react-router';
import { apiFetch } from './api.server';

export async function resolveRedirectOr404(request: Request): Promise<never> {
  const url = new URL(request.url);

  try {
    const response = await apiFetch(`/redirect?path=${encodeURIComponent(url.pathname)}`, { headers: { Accept: 'application/json' } });

    if (response.ok) {
      const { data: target } = (await response.json()) as { data: { to_path: string; status_code: number } };

      throw redirect(target.to_path, target.status_code);
    }
  } catch (error) {
    if (error instanceof Response) {
      throw error;
    }
  }

  throw data({ reason: 'not-found' }, { status: 404 });
}
