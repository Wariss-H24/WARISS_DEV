import { redirect } from 'react-router';
import type { Route } from './+types/language-redirect';
import { preferredLocale } from '~/lib/preferences';

/** « / » redirige vers /fr ou /en : cookie de langue, puis Accept-Language (architecture §4.6). */
export function loader({ request }: Route.LoaderArgs) {
  return redirect(`/${preferredLocale(request)}`, { status: 302, headers: { Vary: 'Cookie, Accept-Language' } });
}

export default function LanguageRedirect() {
  return null;
}
