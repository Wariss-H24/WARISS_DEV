import type { SearchResults, Site } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Kbd } from '@wariss/ui';
import {
  ArrowRight,
  BookOpen,
  Briefcase,
  CornerDownLeft,
  FileDown,
  FlaskConical,
  FolderKanban,
  Home,
  Languages,
  Mail,
  Moon,
  Search,
  Sparkles,
  SquareTerminal,
  Sun,
  User,
  type LucideIcon,
} from 'lucide-react';
import { Dialog as RadixDialog } from 'radix-ui';
import { useCallback, useEffect, useId, useMemo, useRef, useState, type KeyboardEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { otherLocale } from '~/lib/preferences';
import { contentPath, pathTo, type RouteKey } from '~/lib/routes';
import { BROWSER_API_URL, resumeDownloadUrl } from '~/lib/tracking';
import { useAlternatePath } from '../LanguageSwitcher/LanguageSwitcher';
import { applyTheme, useTheme } from '../ThemeToggle/ThemeToggle';
import styles from './CommandPalette.module.css';

const OPEN_EVENT = 'command-palette:open';

/** Ouvre la palette depuis n'importe quel composant (bouton de recherche de l'en-tête). */
export function openCommandPalette(): void {
  window.dispatchEvent(new Event(OPEN_EVENT));
}

interface Command {
  id: string;
  label: string;
  hint?: string;
  icon: LucideIcon;
  group: 'navigation' | 'actions' | 'results';
  run: () => void;
}

const NAVIGATION: Array<[RouteKey, LucideIcon]> = [
  ['home', Home],
  ['about', User],
  ['projects', FolderKanban],
  ['services', Briefcase],
  ['lab', FlaskConical],
  ['blog', BookOpen],
  ['terminal', SquareTerminal],
  ['contact', Mail],
];

/**
 * Recherche plein texte avec délai de frappe. Les résultats sont rattachés au terme cherché :
 * « chargement » = un terme valide dont la réponse n'est pas encore arrivée (aucun état dérivé stocké).
 */
function useSearch(query: string, locale: Locale): { results: SearchResults | null; loading: boolean } {
  const [response, setResponse] = useState<{ term: string; results: SearchResults | null } | null>(null);
  const term = query.trim();
  const searchable = term.length >= 2;

  useEffect(() => {
    if (!searchable) {
      return;
    }

    const controller = new AbortController();
    const timer = window.setTimeout(() => {
      const url = `${BROWSER_API_URL}/public/search?q=${encodeURIComponent(term)}&limit=4&locale=${locale}`;
      fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } })
        .then((answer) => (answer.ok ? answer.json() : { data: {} }))
        .then((body: { data: SearchResults }) => setResponse({ term, results: body.data }))
        .catch((error: unknown) => {
          if ((error as Error).name !== 'AbortError') {
            setResponse({ term, results: null });
          }
        });
    }, 180);

    return () => {
      controller.abort();
      window.clearTimeout(timer);
    };
  }, [term, searchable, locale]);

  if (!searchable) {
    return { results: null, loading: false };
  }

  return { results: response?.results ?? null, loading: response?.term !== term };
}

/**
 * Palette de commandes (⌘K / Ctrl K) : navigation, actions (thème, langue, CV) et recherche plein
 * texte des contenus publiés. Liste au clavier selon le motif « combobox » de l'ARIA APG.
 */
export function CommandPalette({ locale }: { locale: Locale; site: Site | null }) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [open, setOpenState] = useState(false);
  const [query, setQueryState] = useState('');
  const [selected, setActiveIndex] = useState(0);

  // Ouvrir (ou basculer) repart d'une saisie vide ; taper replace la sélection sur le premier résultat.
  const setOpen = useCallback((value: boolean | 'toggle') => {
    setQueryState('');
    setActiveIndex(0);
    setOpenState((current) => (value === 'toggle' ? !current : value));
  }, []);
  const setQuery = (value: string) => {
    setQueryState(value);
    setActiveIndex(0);
  };
  const [theme] = useTheme();
  const alternatePath = useAlternatePath(otherLocale(locale));
  const { results, loading } = useSearch(query, locale);
  const listId = useId();
  const listRef = useRef<HTMLUListElement>(null);

  useEffect(() => {
    const onKeyDown = (event: globalThis.KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        setOpen('toggle');
      }
    };
    const onOpen = () => setOpen(true);
    window.addEventListener('keydown', onKeyDown);
    window.addEventListener(OPEN_EVENT, onOpen);

    return () => {
      window.removeEventListener('keydown', onKeyDown);
      window.removeEventListener(OPEN_EVENT, onOpen);
    };
  }, [setOpen]);

  const go = useCallback(
    (to: string) => {
      setOpen(false);
      navigate(to);
    },
    [navigate, setOpen],
  );

  const commands = useMemo<Command[]>(() => {
    const term = query.trim().toLowerCase();
    const matches = (label: string) => term === '' || label.toLowerCase().includes(term);

    const navigation: Command[] = NAVIGATION.map(([key, icon]) => ({
      id: `nav-${key}`,
      label: t(`nav.${key}`),
      icon,
      group: 'navigation' as const,
      run: () => go(pathTo(locale, key)),
    })).filter((command) => matches(command.label));

    const actions: Command[] = [
      {
        id: 'theme',
        label: theme === 'dark' ? t('theme.light') : t('theme.dark'),
        icon: theme === 'dark' ? Sun : Moon,
        group: 'actions' as const,
        run: () => {
          applyTheme(theme === 'dark' ? 'light' : 'dark');
          setOpen(false);
        },
      },
      { id: 'language', label: t('language.switchTo'), icon: Languages, group: 'actions' as const, run: () => go(alternatePath) },
      {
        id: 'resume',
        label: t('actions.downloadResume'),
        icon: FileDown,
        group: 'actions' as const,
        run: () => {
          setOpen(false);
          window.location.assign(resumeDownloadUrl(locale));
        },
      },
    ].filter((command) => matches(command.label));

    const found: Command[] = [];
    const add = (
      items: Array<{ id: number; title: string; slug: string }> | undefined,
      segment: 'projects' | 'services' | 'lab' | 'blog',
      icon: LucideIcon,
      hint: string,
    ) => {
      for (const item of items ?? []) {
        found.push({ id: `${segment}-${item.id}`, label: item.title, hint, icon, group: 'results', run: () => go(contentPath(locale, segment, item.slug)) });
      }
    };
    add(results?.projects, 'projects', FolderKanban, t('nav.projects'));
    add(results?.services, 'services', Briefcase, t('nav.services'));
    add(results?.experiments, 'lab', FlaskConical, t('nav.lab'));
    add(results?.posts, 'blog', BookOpen, t('nav.blog'));

    return [...found, ...navigation, ...actions];
  }, [query, t, theme, locale, go, alternatePath, results, setOpen]);

  // Les résultats arrivent après la frappe : la sélection reste dans les bornes de la liste.
  const activeIndex = Math.min(selected, Math.max(commands.length - 1, 0));

  useEffect(() => {
    listRef.current?.querySelector('[aria-selected="true"]')?.scrollIntoView({ block: 'nearest' });
  }, [activeIndex]);

  const onKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      setActiveIndex(commands.length ? (activeIndex + 1) % commands.length : 0);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      setActiveIndex(commands.length ? (activeIndex - 1 + commands.length) % commands.length : 0);
    } else if (event.key === 'Enter') {
      event.preventDefault();
      commands[activeIndex]?.run();
    }
  };

  const groups: Array<Command['group']> = ['results', 'navigation', 'actions'];
  const optionId = (index: number) => `${listId}-option-${index}`;

  return (
    <RadixDialog.Root open={open} onOpenChange={setOpen}>
      <RadixDialog.Portal>
        <RadixDialog.Overlay className={styles.overlay} />
        <RadixDialog.Content className={styles.palette} aria-describedby={undefined}>
          <RadixDialog.Title className="visually-hidden">{t('command.title')}</RadixDialog.Title>
          <div className={styles.inputRow}>
            <Search aria-hidden="true" size={18} className={styles.inputIcon} />
            <input
              className={styles.input}
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              onKeyDown={onKeyDown}
              placeholder={t('command.placeholder')}
              role="combobox"
              aria-expanded="true"
              aria-controls={listId}
              aria-activedescendant={commands.length ? optionId(activeIndex) : undefined}
              aria-autocomplete="list"
              autoComplete="off"
              spellCheck={false}
            />
            {loading ? <span className={styles.spinner} aria-label={t('command.searching')} /> : <Kbd>Esc</Kbd>}
          </div>

          <ul id={listId} ref={listRef} role="listbox" aria-label={t('command.title')} className={styles.list}>
            {commands.length === 0 ? (
              <li className={styles.empty} role="presentation">
                <Sparkles aria-hidden="true" size={18} />
                {t('command.noResults', { query })}
              </li>
            ) : (
              groups.map((group) => {
                const items = commands.map((command, index) => ({ command, index })).filter(({ command }) => command.group === group);

                if (items.length === 0) {
                  return null;
                }

                return (
                  <li key={group} role="presentation">
                    <p className={styles.groupLabel} aria-hidden="true">
                      {t(`command.${group}`)}
                    </p>
                    <ul role="group" aria-label={t(`command.${group}`)} className={styles.group}>
                      {items.map(({ command, index }) => {
                        const Icon = command.icon;

                        return (
                          <li
                            key={command.id}
                            id={optionId(index)}
                            role="option"
                            aria-selected={index === activeIndex}
                            className={styles.option}
                            onPointerMove={() => setActiveIndex(index)}
                            onClick={command.run}
                            onKeyDown={(event) => event.key === 'Enter' && command.run()}
                          >
                            <Icon aria-hidden="true" size={16} className={styles.optionIcon} />
                            <span className={styles.optionLabel}>{command.label}</span>
                            {command.hint ? <span className={styles.optionHint}>{command.hint}</span> : null}
                            {index === activeIndex ? (
                              <CornerDownLeft aria-hidden="true" size={14} className={styles.enter} />
                            ) : (
                              <ArrowRight aria-hidden="true" size={14} className={styles.arrow} />
                            )}
                          </li>
                        );
                      })}
                    </ul>
                  </li>
                );
              })
            )}
          </ul>

          <div className={styles.footer} aria-hidden="true">
            <span>
              <Kbd>↑</Kbd>
              <Kbd>↓</Kbd> {t('command.move')}
            </span>
            <span>
              <Kbd>↵</Kbd> {t('command.select')}
            </span>
            <span>
              <Kbd>⌘</Kbd>
              <Kbd>K</Kbd> {t('command.hint')}
            </span>
          </div>
        </RadixDialog.Content>
      </RadixDialog.Portal>
    </RadixDialog.Root>
  );
}
