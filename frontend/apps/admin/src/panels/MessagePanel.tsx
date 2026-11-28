import { Badge, Button, Card } from '@wariss/ui';
import { Mail, Reply } from 'lucide-react';
import type { Item } from '~/crud/api';
import { MESSAGE_TYPE, optionLabel } from '~/crud/options';
import { formatDateTime } from '~/lib/format';
import styles from './panels.module.css';

/** Contenu d'un message reçu (lecture seule : le texte du visiteur n'est jamais modifié). */
export function MessagePanel({ message }: { message: Item }) {
  const details = (message.details ?? {}) as Record<string, string>;
  const subject = String(message.subject ?? 'Votre message');

  return (
    <Card padded className={styles.panel}>
      <div className={styles.panelHead}>
        <Mail aria-hidden="true" size={16} />
        <h2 className={styles.panelTitle}>Message</h2>
        <Badge mono>{optionLabel(MESSAGE_TYPE, message.type)}</Badge>
      </div>
      <dl className={styles.dl}>
        <div>
          <dt>De</dt>
          <dd>{String(message.name)}</dd>
        </div>
        <div>
          <dt>Adresse</dt>
          <dd>
            <a href={`mailto:${String(message.email)}`} className={styles.link}>
              {String(message.email)}
            </a>
          </dd>
        </div>
        <div>
          <dt>Reçu le</dt>
          <dd>{formatDateTime(message.created_at as string)}</dd>
        </div>
        <div>
          <dt>Langue</dt>
          <dd>{String(message.locale).toUpperCase()}</dd>
        </div>
        {Object.entries(details).map(([key, value]) => (
          <div key={key}>
            <dt>{key === 'budget' ? 'Budget' : key === 'deadline' ? 'Échéance' : key}</dt>
            <dd>{value}</dd>
          </div>
        ))}
      </dl>
      {message.subject ? <p className={styles.messageSubject}>{subject}</p> : null}
      <p className={styles.messageBody}>{String(message.body ?? '')}</p>
      <Button asChild size="sm">
        <a href={`mailto:${String(message.email)}?subject=${encodeURIComponent(`Re: ${subject}`)}`}>
          <Reply aria-hidden="true" />
          Répondre par e-mail
        </a>
      </Button>
    </Card>
  );
}
