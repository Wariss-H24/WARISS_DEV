import { Badge, Button, Dialog, Tooltip } from '@wariss/ui';
import { ExternalLink, LogOut, Menu, Moon, Sun } from 'lucide-react';
import { useState, useSyncExternalStore } from 'react';
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router';
import { useAbilities, useCurrentUser, useLogout } from '~/lib/auth';
import { SITE_URL } from '~/lib/http';
import { NAVIGATION } from './navigation';
import styles from './AdminLayout.module.css';

const THEME_KEY = 'admin-theme';

function subscribe(onChange: () => void) {
  window.addEventListener('admin-themechange', onChange);

  return () => window.removeEventListener('admin-themechange', onChange);
}

function useAdminTheme(): ['light' | 'dark', () => void] {
  const theme = useSyncExternalStore(
    subscribe,
    () => (document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light'),
    () => 'light' as const,
  );

  const toggle = () => {
    const next = theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;

    try {
      localStorage.setItem(THEME_KEY, next);
    } catch {
      // Préférence de confort : sans stockage, le thème reste celui de la session.
    }

    window.dispatchEvent(new Event('admin-themechange'));
  };

  return [theme, toggle];
}

function Sidebar({ onNavigate }: { onNavigate?: () => void }) {
  const { canViewSensitive } = useAbilities();

  return (
    <nav aria-label="Navigation de l’administration" className={styles.nav}>
      {NAVIGATION.map((group) => {
        const entries = group.entries.filter((entry) => !entry.sensitive || canViewSensitive);

        return entries.length ? (
          <div key={group.title || 'main'} className={styles.group}>
            {group.title ? <p className={styles.groupTitle}>{group.title}</p> : null}
            <ul className={styles.list}>
              {entries.map((entry) => {
                const Icon = entry.icon;

                return (
                  <li key={entry.to}>
                    <NavLink
                      to={entry.to}
                      end={entry.to === '/'}
                      className={({ isActive }) => `${styles.link} ${isActive ? styles.active : ''}`}
                      onClick={onNavigate}
                    >
                      <Icon aria-hidden="true" size={16} />
                      <span>{entry.label}</span>
                    </NavLink>
                  </li>
                );
              })}
            </ul>
          </div>
        ) : null;
      })}
    </nav>
  );
}

/** Mise en page de l'administration : barre latérale, barre supérieure, contenu. */
export function AdminLayout() {
  const { data: user } = useCurrentUser();
  const logout = useLogout();
  const navigate = useNavigate();
  const location = useLocation();
  const [theme, toggleTheme] = useAdminTheme();
  const [menuOpenedAt, setMenuOpenedAt] = useState<string | null>(null);

  return (
    <div className={styles.shell}>
      <a href="#admin-content" className={styles.skip}>
        Aller au contenu
      </a>
      <aside className={styles.sidebar}>
        <div className={styles.brand}>
          <span className={styles.logo} aria-hidden="true">
            W
          </span>
          <span>
            <strong>wariss.dev</strong>
            <span className={styles.brandSub}>Administration</span>
          </span>
        </div>
        <Sidebar />
      </aside>

      <div className={styles.main}>
        <header className={styles.topbar}>
          <Dialog
            side
            open={menuOpenedAt === location.pathname}
            onOpenChange={(open) => setMenuOpenedAt(open ? location.pathname : null)}
            title="Navigation"
            closeLabel="Fermer le menu"
            trigger={
              <Button variant="ghost" size="sm" iconOnly aria-label="Ouvrir le menu" className={styles.menuButton}>
                <Menu aria-hidden="true" />
              </Button>
            }
          >
            <Sidebar onNavigate={() => setMenuOpenedAt(null)} />
          </Dialog>

          <div className={styles.topbarEnd}>
            {user?.role === 'viewer' ? <Badge tone="warning">Lecture seule</Badge> : null}
            <Tooltip content="Voir le site public">
              <Button asChild variant="ghost" size="sm">
                <a href={SITE_URL} target="_blank" rel="noopener noreferrer">
                  <ExternalLink aria-hidden="true" />
                  <span className={styles.hideSmall}>Voir le site</span>
                </a>
              </Button>
            </Tooltip>
            <Tooltip content={theme === 'dark' ? 'Thème clair' : 'Thème sombre'}>
              <Button variant="ghost" size="sm" iconOnly onClick={toggleTheme} aria-label="Changer de thème">
                {theme === 'dark' ? <Sun aria-hidden="true" /> : <Moon aria-hidden="true" />}
              </Button>
            </Tooltip>
            <div className={styles.user}>
              <span className={styles.avatar} aria-hidden="true">
                {user?.name.charAt(0).toUpperCase()}
              </span>
              <span className={styles.hideSmall}>{user?.name}</span>
            </div>
            <Tooltip content="Se déconnecter">
              <Button
                variant="ghost"
                size="sm"
                iconOnly
                aria-label="Se déconnecter"
                loading={logout.isPending}
                onClick={() => logout.mutate(undefined, { onSettled: () => navigate('/login', { replace: true }) })}
              >
                <LogOut aria-hidden="true" />
              </Button>
            </Tooltip>
          </div>
        </header>

        <main id="admin-content" className={styles.content} tabIndex={-1}>
          <Outlet />
        </main>
      </div>
    </div>
  );
}
