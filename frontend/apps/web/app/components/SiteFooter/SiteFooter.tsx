import type { MenuItem, Site } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Badge, Container } from '@wariss/ui';
import { ArrowUp, ArrowUpRight } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { menuItemHref, pathTo, type RouteKey } from '~/lib/routes';
import { Logo } from '../Logo/Logo';
import { SocialIcon } from '../SocialIcon/SocialIcon';
import styles from './SiteFooter.module.css';

const DEFAULT_EXPLORE: RouteKey[] = ['about', 'projects', 'services', 'blog', 'resume', 'contact'];
const DEFAULT_LEGAL: RouteKey[] = ['legal-notice', 'privacy'];

function FooterLinks({ locale, items, fallback }: { locale: Locale; items: MenuItem[] | undefined; fallback: RouteKey[] }) {
  const { t } = useTranslation();

  if (!items?.length) {
    return fallback.map((key) => (
      <li key={key}>
        <Link to={pathTo(locale, key)} className={styles.link} prefetch="intent">
          {t(`nav.${key}`)}
        </Link>
      </li>
    ));
  }

  return items.map((item, index) => {
    const { href, external } = menuItemHref(locale, item);
    const label = item.label ?? t(`nav.${(item.route_key ?? item.page?.key ?? 'home') as 'home'}`);

    return (
      <li key={`${index}-${href}`}>
        {external ? (
          <a href={href} className={styles.link} target={item.opens_in_new_tab ? '_blank' : undefined} rel="noopener noreferrer">
            {label}
            <ArrowUpRight aria-hidden="true" size={13} />
          </a>
        ) : (
          <Link to={href} className={styles.link} prefetch="intent">
            {label}
          </Link>
        )}
      </li>
    );
  });
}

export function SiteFooter({ locale, site }: { locale: Locale; site: Site | null }) {
  const { t } = useTranslation();
  const profile = site?.profile;
  const name = profile?.display_name ?? t('brand');
  const tagline = (site?.settings.general?.tagline as string | null | undefined) ?? t('footer.tagline');
  const footerText = site?.settings.footer?.text as string | null | undefined;
  const year = new Date().getFullYear();

  return (
    <footer className={styles.footer}>
      <div className={styles.glow} aria-hidden="true" />
      <Container size="wide" className={styles.grid}>
        <div className={styles.about}>
          <Link to={pathTo(locale, 'home')} className={styles.brand} aria-label={t('nav.home')}>
            <Logo />
          </Link>
          <p className={styles.tagline}>{tagline}</p>
          {profile ? (
            <Badge
              tone={profile.availability_status === 'available' ? 'success' : profile.availability_status === 'limited' ? 'warning' : 'neutral'}
              dot="pulse"
            >
              {t(`availability.${profile.availability_status}`)}
            </Badge>
          ) : null}
        </div>

        <nav aria-label={t('footer.explore')} className={styles.column}>
          <p className={styles.heading}>{t('footer.explore')}</p>
          <ul className={styles.list}>
            <FooterLinks locale={locale} items={site?.menus.footer?.items} fallback={DEFAULT_EXPLORE} />
          </ul>
        </nav>

        {profile?.social_links.length ? (
          <div className={styles.column}>
            <p className={styles.heading}>{t('footer.connect')}</p>
            <ul className={styles.list}>
              {profile.social_links.map((link) => (
                <li key={link.id}>
                  <a href={link.url} className={styles.link} target="_blank" rel="noopener noreferrer me">
                    <SocialIcon platform={link.platform} size={15} />
                    <span className={styles.platform}>{link.platform}</span>
                  </a>
                </li>
              ))}
            </ul>
          </div>
        ) : null}

        <nav aria-label={t('footer.legal')} className={styles.column}>
          <p className={styles.heading}>{t('footer.legal')}</p>
          <ul className={styles.list}>
            <FooterLinks locale={locale} items={site?.menus.legal?.items} fallback={DEFAULT_LEGAL} />
          </ul>
        </nav>
      </Container>

      <Container size="wide" className={styles.bottom}>
        <p>{footerText || t('footer.rights', { year, name })}</p>
        <p className={styles.built}>{t('footer.builtWith')}</p>
        <a href="#content" className={styles.top}>
          <ArrowUp aria-hidden="true" size={14} />
          {t('footer.backToTop')}
        </a>
      </Container>
    </footer>
  );
}
