import type { MenuItem, Site } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Button, Dialog, Kbd, Tooltip } from '@wariss/ui';
import { ArrowUpRight, Menu, Search, SquareTerminal } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, NavLink, useLocation } from 'react-router';
import { menuItemHref, pathTo, type RouteKey } from '~/lib/routes';
import { openCommandPalette } from '../CommandPalette/CommandPalette';
import { LanguageSwitcher } from '../LanguageSwitcher/LanguageSwitcher';
import { Logo } from '../Logo/Logo';
import { ThemeToggle } from '../ThemeToggle/ThemeToggle';
import styles from './SiteHeader.module.css';

/** Navigation par défaut (cahier des charges §24), si aucun menu « header » n'est configuré dans l'administration. L'accueil passe par le logo, le contact par le bouton dédié. */
const DEFAULT_NAV: RouteKey[] = ['about', 'skills', 'experience', 'projects', 'services', 'lab', 'blog', 'achievements'];

interface NavEntry {
  key: string;
  label: string;
  href: string;
  external: boolean;
  newTab: boolean;
}

function useNavigation(locale: Locale, site: Site | null): NavEntry[] {
  const { t } = useTranslation();
  const items: MenuItem[] | undefined = site?.menus.header?.items;

  if (!items?.length) {
    return DEFAULT_NAV.map((key) => ({ key, label: t(`nav.${key}`), href: pathTo(locale, key), external: false, newTab: false }));
  }

  return items
    .filter((item) => item.route_key !== 'home' && item.page?.key !== 'home' && item.route_key !== 'contact' && item.page?.key !== 'contact')
    .map((item, index) => {
      const { href, external } = menuItemHref(locale, item);
      const fallbackKey = item.route_key ?? item.page?.key ?? '';

      return {
        key: `${index}-${href}`,
        label: item.label ?? (fallbackKey ? t(`nav.${fallbackKey}` as 'nav.home') : href),
        href,
        external,
        newTab: item.opens_in_new_tab,
      };
    });
}

export function SiteHeader({ locale, site }: { locale: Locale; site: Site | null }) {
  const { t } = useTranslation();
  const navigation = useNavigation(locale, site);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  // Le menu mobile est ouvert « sur une adresse » : il se ferme de lui-même à la navigation.
  const [menuOpenedAt, setMenuOpenedAt] = useState<string | null>(null);
  const menuOpen = menuOpenedAt === location.pathname;
  const setMenuOpen = (open: boolean) => setMenuOpenedAt(open ? location.pathname : null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const links = (variant: 'bar' | 'sheet') =>
    navigation.map((entry) =>
      entry.external ? (
        <a key={entry.key} href={entry.href} className={styles[`${variant}Link`]} target={entry.newTab ? '_blank' : undefined} rel="noopener noreferrer">
          {entry.label}
          <ArrowUpRight aria-hidden="true" size={14} />
        </a>
      ) : (
        <NavLink key={entry.key} to={entry.href} className={({ isActive }) => `${styles[`${variant}Link`]} ${isActive ? styles.active : ''}`} prefetch="intent">
          {entry.label}
        </NavLink>
      ),
    );

  return (
    <>
      <a href="#content" className={styles.skip}>
        {t('skipToContent')}
      </a>
      <header className={styles.header} data-scrolled={scrolled || undefined}>
        <div className={styles.inner}>
          <Link to={pathTo(locale, 'home')} className={styles.brand} aria-label={`${t('brand')} · ${t('nav.home')}`}>
            <Logo />
          </Link>

          <nav aria-label={t('nav.mainNavigation')} className={styles.nav}>
            {links('bar')}
          </nav>

          <div className={styles.actions}>
            <button type="button" className={styles.search} onClick={openCommandPalette} aria-label={t('command.open')}>
              <Search aria-hidden="true" size={16} />
              <span className={styles.searchLabel}>{t('actions.search')}</span>
              <span className={styles.searchKeys} aria-hidden="true">
                <Kbd>⌘</Kbd>
                <Kbd>K</Kbd>
              </span>
            </button>
            <LanguageSwitcher locale={locale} className={styles.desktopOnly} />
            <ThemeToggle className={styles.desktopOnly} />
            <Tooltip content={t('nav.terminal')}>
              <NavLink
                to={pathTo(locale, 'terminal')}
                prefetch="intent"
                aria-label={t('nav.terminal')}
                className={({ isActive }) => `${styles.terminal} ${isActive ? styles.terminalActive : ''}`}
              >
                <SquareTerminal aria-hidden="true" size={18} />
              </NavLink>
            </Tooltip>
            <Button asChild size="sm" className={styles.cta}>
              <Link to={pathTo(locale, 'contact')} prefetch="intent">
                {t('actions.contactMe')}
              </Link>
            </Button>
            <Dialog
              open={menuOpen}
              onOpenChange={setMenuOpen}
              side
              title={t('nav.menu')}
              closeLabel={t('nav.closeMenu')}
              trigger={
                <Button variant="ghost" size="sm" iconOnly className={styles.menuButton} aria-label={t('nav.openMenu')}>
                  <Menu aria-hidden="true" />
                </Button>
              }
            >
              <nav aria-label={t('nav.mainNavigation')} className={styles.sheetNav}>
                <NavLink to={pathTo(locale, 'home')} end className={({ isActive }) => `${styles.sheetLink} ${isActive ? styles.active : ''}`}>
                  {t('nav.home')}
                </NavLink>
                {links('sheet')}
                <NavLink to={pathTo(locale, 'contact')} className={({ isActive }) => `${styles.sheetLink} ${isActive ? styles.active : ''}`}>
                  {t('nav.contact')}
                </NavLink>
              </nav>
              <div className={styles.sheetFooter}>
                <LanguageSwitcher locale={locale} />
                <ThemeToggle />
              </div>
            </Dialog>
          </div>
        </div>
      </header>
    </>
  );
}
