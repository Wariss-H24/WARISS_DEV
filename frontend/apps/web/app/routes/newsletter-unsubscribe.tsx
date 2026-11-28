import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/newsletter-unsubscribe';
import { NewsletterAction } from '~/features/newsletter/NewsletterAction';
import { routeLocale } from '~/lib/route-helpers';

export function loader({ params, request }: Route.LoaderArgs) {
  const url = new URL(request.url);
  const id = url.searchParams.get('id');
  const token = url.searchParams.get('token');
  const valid = id && /^\d{1,18}$/.test(id) && token && /^[0-9a-f]{64}$/.test(token);

  return { locale: routeLocale(params), body: valid ? { id, token } : null };
}

export const meta: Route.MetaFunction = () => [{ title: 'Newsletter' }, { name: 'robots', content: 'noindex, nofollow' }];

export default function NewsletterUnsubscribe() {
  const { locale, body } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <NewsletterAction
      locale={locale}
      endpoint="newsletter/unsubscribe"
      body={body}
      labels={{
        pending: t('newsletter.unsubscribing'),
        success: t('newsletter.unsubscribed'),
        failure: t('newsletter.unsubscribeFailed'),
        home: t('errors.goHome'),
      }}
    />
  );
}
