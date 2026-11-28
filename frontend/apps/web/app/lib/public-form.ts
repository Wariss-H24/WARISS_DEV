/**
 * Envoi des formulaires publics directement depuis le navigateur vers l'API : la limite de fréquence
 * et la vérification anti-robot s'appliquent au visiteur réel, pas au serveur du site.
 */
import type { Locale } from '@wariss/i18n';
import { BROWSER_API_URL } from './tracking';

export type FormResult =
  { status: 'success'; message: string } | { status: 'invalid'; message: string; errors: Record<string, string> } | { status: 'error'; message: string | null };

export async function submitPublicForm(path: string, locale: Locale, body: Record<string, unknown>): Promise<FormResult> {
  let response: Response;

  try {
    response = await fetch(`${BROWSER_API_URL}/public/${path}?locale=${locale}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'Accept-Language': locale },
      body: JSON.stringify(body),
      credentials: 'omit',
    });
  } catch {
    return { status: 'error', message: null };
  }

  const payload = (await response.json().catch(() => ({}))) as { message?: string; errors?: Record<string, string[]> };

  if (response.ok) {
    return { status: 'success', message: payload.message ?? '' };
  }

  if (response.status === 422 && payload.errors) {
    const errors: Record<string, string> = {};

    for (const [field, messages] of Object.entries(payload.errors)) {
      errors[field.split('.')[0] ?? field] ??= messages[0] ?? '';
    }

    return { status: 'invalid', message: payload.message ?? '', errors };
  }

  return { status: 'error', message: payload.message ?? null };
}
