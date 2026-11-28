import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/newsletter-confirm';
import { NewsletterAction } from '~/features/newsletter/NewsletterAction';
import { routeLocale } from '~/lib/route-helpers';

export function loader({ params, request }: Route.LoaderArgs) {
  const token = new URL(request.url).searchParams.get('token');

  return { locale: routeLocale(params), token: token && /^[A-Za-z0-9]{64}$/.test(token) ? token : null };
}

export const meta: Route.MetaFunction = () => [{ title: 'Newsletter' }, { name: 'robots', content: 'noindex, nofollow' }];

export default function NewsletterConfirm() {
  const { locale, token } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <NewsletterAction
      locale={locale}
      endpoint="newsletter/confirm"
      body={token ? { token } : null}
      labels={{ pending: t('newsletter.confirming'), success: t('newsletter.confirmed'), failure: t('newsletter.confirmFailed'), home: t('errors.goHome') }}
    />
  );
}
