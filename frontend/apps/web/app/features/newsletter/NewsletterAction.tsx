import type { Locale } from '@wariss/i18n';
import { AuroraBackground, Button, Container } from '@wariss/ui';
import { CircleCheck, CircleX, LoaderCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router';
import { submitPublicForm } from '~/lib/public-form';
import { pathTo } from '~/lib/routes';
import styles from './NewsletterAction.module.css';

interface NewsletterActionProps {
  locale: Locale;
  endpoint: 'newsletter/confirm' | 'newsletter/unsubscribe';
  body: Record<string, string> | null;
  labels: { pending: string; success: string; failure: string; home: string };
}

/**
 * Page d'action de la newsletter (confirmation, désinscription) : la demande part du navigateur
 * une fois la page affichée, jamais au simple chargement par un robot d'aperçu de lien.
 */
export function NewsletterAction({ locale, endpoint, body, labels }: NewsletterActionProps) {
  const [state, setState] = useState<'pending' | 'success' | 'failure'>(body ? 'pending' : 'failure');
  const sent = useRef(false);

  useEffect(() => {
    if (!body || sent.current) {
      return;
    }

    sent.current = true;
    void submitPublicForm(endpoint, locale, body).then((result) => setState(result.status === 'success' ? 'success' : 'failure'));
  }, [body, endpoint, locale]);

  const Icon = state === 'pending' ? LoaderCircle : state === 'success' ? CircleCheck : CircleX;

  return (
    <section className={styles.page} aria-live="polite">
      <AuroraBackground />
      <Container size="reading" className={styles.inner}>
        <Icon aria-hidden="true" size={48} className={styles[state]} />
        <h1 className={styles.title}>{labels[state]}</h1>
        {state === 'pending' ? null : (
          <Button asChild variant="secondary">
            <Link to={pathTo(locale, 'home')}>{labels.home}</Link>
          </Button>
        )}
      </Container>
    </section>
  );
}
