import type { ShareNetwork } from '@wariss/api-client';
import { Button, Tooltip } from '@wariss/ui';
import { Check, Link2, Mail, MessageCircle } from 'lucide-react';
import { useState } from 'react';
import { useLocation, useRouteLoaderData } from 'react-router';
import { useTranslation } from 'react-i18next';
import { trackShare, type TrackedContent } from '~/lib/tracking';
import { SocialIcon } from '../SocialIcon/SocialIcon';
import styles from './ShareButtons.module.css';

/** Ajoute le balisage de partage (D-16) : la visite issue d'un partage est reconnue sans cookie. */
function sharedUrl(url: string, network: ShareNetwork): string {
  const target = new URL(url);
  target.searchParams.set('utm_source', network);
  target.searchParams.set('utm_medium', 'share');

  return target.toString();
}

function shareHref(network: ShareNetwork, url: string, title: string): string {
  const link = encodeURIComponent(sharedUrl(url, network));
  const text = encodeURIComponent(title);

  switch (network) {
    case 'linkedin':
      return `https://www.linkedin.com/sharing/share-offsite/?url=${link}`;
    case 'x':
      return `https://x.com/intent/post?url=${link}&text=${text}`;
    case 'facebook':
      return `https://www.facebook.com/sharer/sharer.php?u=${link}`;
    case 'whatsapp':
      return `https://wa.me/?text=${text}%20${link}`;
    case 'email':
      return `mailto:?subject=${text}&body=${link}`;
    default:
      return url;
  }
}

/** Boutons de partage, chacun mesuré (événement share + réseau). */
export function ShareButtons({ title, content, label }: { title: string; content?: TrackedContent | null; label?: string }) {
  const { t } = useTranslation();
  const root = useRouteLoaderData<{ siteUrl: string }>('root');
  const location = useLocation();
  const url = `${root?.siteUrl ?? ''}${location.pathname}`;
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(sharedUrl(url, 'copy_link'));
      setCopied(true);
      trackShare('copy_link', content);
      window.setTimeout(() => setCopied(false), 2000);
    } catch {
      // Le presse-papiers peut être refusé : rien à faire.
    }
  };

  const networks: Array<[ShareNetwork, React.ReactNode]> = [
    ['linkedin', <SocialIcon key="l" platform="linkedin" size={16} />],
    ['x', <SocialIcon key="x" platform="x" size={15} />],
    ['whatsapp', <MessageCircle key="w" aria-hidden="true" />],
    ['email', <Mail key="e" aria-hidden="true" />],
  ];

  return (
    <div className={styles.share}>
      {label ? <p className="mono-label">{label}</p> : null}
      <ul className={styles.list}>
        {networks.map(([network, icon]) => (
          <li key={network}>
            <Tooltip content={t(`share.${network}`)}>
              <Button asChild variant="secondary" size="sm" iconOnly>
                <a
                  href={url ? shareHref(network, url, title) : '#'}
                  target={network === 'email' ? undefined : '_blank'}
                  rel="noopener noreferrer"
                  aria-label={t(`share.${network}`)}
                  onClick={() => trackShare(network, content)}
                >
                  {icon}
                </a>
              </Button>
            </Tooltip>
          </li>
        ))}
        <li>
          <Tooltip content={copied ? t('actions.copied') : t('share.copy_link')}>
            <Button variant="secondary" size="sm" iconOnly onClick={copy} aria-label={t('share.copy_link')}>
              {copied ? <Check aria-hidden="true" /> : <Link2 aria-hidden="true" />}
            </Button>
          </Tooltip>
        </li>
      </ul>
      <span className="visually-hidden" aria-live="polite">
        {copied ? t('actions.copied') : ''}
      </span>
    </div>
  );
}
