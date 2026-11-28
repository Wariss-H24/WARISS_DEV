import { Button, Tooltip } from '@wariss/ui';
import { Moon, Sun } from 'lucide-react';
import { useSyncExternalStore } from 'react';
import { useTranslation } from 'react-i18next';
import { useRouteLoaderData } from 'react-router';
import { THEME_COOKIE, writePreference, type Theme } from '~/lib/preferences';

const EVENT = 'themechange';

/** Applique un thème immédiatement et le mémorise (cookie lu par le serveur au prochain chargement). */
export function applyTheme(theme: Theme): void {
  document.documentElement.dataset.theme = theme;
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'light' ? '#f5f7fb' : '#070b16');
  writePreference(THEME_COOKIE, theme);
  window.dispatchEvent(new Event(EVENT));
}

function subscribe(onChange: () => void): () => void {
  window.addEventListener(EVENT, onChange);

  return () => window.removeEventListener(EVENT, onChange);
}

function snapshot(): Theme {
  return document.documentElement.dataset.theme === 'light' ? 'light' : 'dark';
}

/** Thème courant : lu dans le document, avec la valeur du cookie (loader racine) côté serveur. */
export function useTheme(): [Theme, () => void] {
  const root = useRouteLoaderData<{ theme: Theme }>('root');
  const theme = useSyncExternalStore(subscribe, snapshot, () => root?.theme ?? 'dark');

  return [theme, () => applyTheme(theme === 'dark' ? 'light' : 'dark')];
}

export function ThemeToggle({ className }: { className?: string }) {
  const { t } = useTranslation();
  const [theme, toggle] = useTheme();
  const label = theme === 'dark' ? t('theme.light') : t('theme.dark');

  return (
    <Tooltip content={label}>
      <Button variant="ghost" size="sm" iconOnly onClick={toggle} aria-label={t('theme.toggle')} className={className}>
        {theme === 'dark' ? <Sun aria-hidden="true" /> : <Moon aria-hidden="true" />}
      </Button>
    </Tooltip>
  );
}
