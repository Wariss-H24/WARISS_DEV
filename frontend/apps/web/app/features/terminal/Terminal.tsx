import type { Experience, Profile, Project, Technology } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { useEffect, useRef, useState, type KeyboardEvent, type ReactNode } from 'react';
import { Link, useNavigate } from 'react-router';
import { applyTheme } from '~/components/ThemeToggle/ThemeToggle';
import { formatPeriod } from '~/lib/format';
import { contentPath, pathTo } from '~/lib/routes';
import { DEFAULT_SOCIAL_LINKS } from '~/lib/social';
import { resumeDownloadUrl } from '~/lib/tracking';
import styles from './Terminal.module.css';

export interface TerminalData {
  profile: Profile | null;
  projects: Project[];
  technologies: Technology[];
  experiences: Experience[];
}

interface Entry {
  id: number;
  /** Écran d'accueil, rendu à la volée (horloge en direct). */
  banner?: boolean;
  command?: string;
  /** Numéro de la commande et heure d'exécution, affichés dans l'invite. */
  index?: number;
  time?: string;
  output?: ReactNode;
}

const VERSION = 'v1.0.0';

const LOGO = [
  '██╗    ██╗ █████╗ ██████╗ ██╗███████╗███████╗',
  '██║    ██║██╔══██╗██╔══██╗██║██╔════╝██╔════╝',
  '██║ █╗ ██║███████║██████╔╝██║███████╗███████╗',
  '██║███╗██║██╔══██║██╔══██╗██║╚════██║╚════██║',
  '╚███╔███╔╝██║  ██║██║  ██║██║███████║███████║',
  ' ╚══╝╚══╝ ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝╚══════╝╚══════╝',
].join('\n');

/** Commandes mises en avant sur l'écran d'accueil. */
const QUICK_START = ['about', 'projects', 'skills', 'experience', 'contact'] as const;

const TEXT = {
  fr: {
    tagline: 'PORTFOLIO · Interface terminal',
    start: 'Pour commencer',
    clickHint: 'cliquez sur une commande ou tapez-la',
    helpBefore: 'Tapez',
    helpAfter: 'puis appuyez sur',
    helpEnd: 'pour voir toutes les commandes.',
    enter: 'Entrée',
    status: (projects: number, technologies: number) => `${projects} projets · ${technologies} technologies · données en direct`,
    unknown: (command: string) => `Commande inconnue : ${command}. Tapez « help » puis Entrée.`,
    label: 'Saisir une commande, puis appuyer sur Entrée',
    placeholder: 'tapez help puis Entrée…',
    footer: { run: 'exécuter', history: 'historique', complete: 'compléter', clear: 'effacer' },
    help: {
      help: 'Liste des commandes',
      whoami: 'Qui suis-je ?',
      about: 'Ma présentation',
      skills: 'Mon stack technique',
      projects: 'Mes projets récents',
      experience: 'Mon parcours',
      contact: 'Me contacter',
      resume: 'Télécharger mon CV',
      github: 'Ouvrir mon GitHub',
      social: 'Mes réseaux',
      theme: 'Changer de thème (theme light | dark)',
      lang: 'Passer en anglais',
      open: 'Ouvrir une page (open projects)',
      date: 'Date et heure',
      banner: 'Réafficher l’écran d’accueil',
      clear: 'Effacer l’écran (Ctrl + L)',
    },
    helpFooter: 'Astuce : cliquez sur une commande, ou tapez-la puis Entrée. ↑ ↓ pour l’historique, Tab pour compléter.',
    noData: 'Aucune donnée pour le moment.',
    opening: (target: string) => `Ouverture de ${target}…`,
    present: 'aujourd’hui',
    sudo: 'Bien essayé. 😄',
  },
  en: {
    tagline: 'PORTFOLIO · Terminal interface',
    start: 'Getting started',
    clickHint: 'click a command or type it',
    helpBefore: 'Type',
    helpAfter: 'then press',
    helpEnd: 'to see every command.',
    enter: 'Enter',
    status: (projects: number, technologies: number) => `${projects} projects · ${technologies} technologies · live data`,
    unknown: (command: string) => `Unknown command: ${command}. Type “help” and press Enter.`,
    label: 'Type a command, then press Enter',
    placeholder: 'type help and press Enter…',
    footer: { run: 'run', history: 'history', complete: 'complete', clear: 'clear' },
    help: {
      help: 'List the commands',
      whoami: 'Who am I?',
      about: 'About me',
      skills: 'My tech stack',
      projects: 'My recent projects',
      experience: 'My experience',
      contact: 'Get in touch',
      resume: 'Download my resume',
      github: 'Open my GitHub',
      social: 'My social profiles',
      theme: 'Switch theme (theme light | dark)',
      lang: 'Switch to French',
      open: 'Open a page (open projects)',
      date: 'Date and time',
      banner: 'Show the welcome screen again',
      clear: 'Clear the screen (Ctrl + L)',
    },
    helpFooter: 'Tip: click a command, or type it and press Enter. ↑ ↓ for history, Tab to complete.',
    noData: 'No data yet.',
    opening: (target: string) => `Opening ${target}…`,
    present: 'present',
    sudo: 'Nice try. 😄',
  },
} as const;

const pad = (value: number) => String(value).padStart(2, '0');
const clockOf = (date: Date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`;

/** Heure courante, uniquement côté navigateur (null au rendu serveur : pas d'écart d'hydratation). */
function useNow(): Date | null {
  const [now, setNow] = useState<Date | null>(null);

  useEffect(() => {
    const first = window.setTimeout(() => setNow(new Date()), 0);
    const timer = window.setInterval(() => setNow(new Date()), 15_000);

    return () => {
      window.clearTimeout(first);
      window.clearInterval(timer);
    };
  }, []);

  return now;
}

/**
 * Terminal interactif (cahier des charges §21.2 ter) : simulation dans le navigateur, construite à
 * partir des données publiques de l'API. Aucune commande système n'est exécutée. Écran d'accueil
 * avec commandes cliquables, historique avec les flèches, complétion avec Tab, journal annoncé aux
 * lecteurs d'écran.
 */
export function Terminal({ locale, data }: { locale: Locale; data: TerminalData }) {
  const text = TEXT[locale];
  const navigate = useNavigate();
  const now = useNow();
  const [entries, setEntries] = useState<Entry[]>([{ id: 0, banner: true }]);
  const [input, setInput] = useState('');
  const [history, setHistory] = useState<string[]>([]);
  const [cursor, setCursor] = useState(-1);
  const [count, setCount] = useState(1);
  const inputRef = useRef<HTMLInputElement>(null);
  const screenRef = useRef<HTMLDivElement>(null);
  const nextId = useRef(1);
  const socialLinks = data.profile?.social_links.length ? data.profile.social_links : DEFAULT_SOCIAL_LINKS;
  const github = socialLinks.find((link) => link.platform.toLowerCase() === 'github');
  const dateLocale = locale === 'fr' ? 'fr-FR' : 'en-GB';

  useEffect(() => {
    const screen = screenRef.current;

    if (screen && entries.length > 1) {
      screen.scrollTop = screen.scrollHeight;
    }
  }, [entries]);

  const commandButton = (name: string) => (
    <button type="button" className={styles.command} onClick={() => run(name)}>
      {name}
    </button>
  );

  const commands: Record<string, (args: string[]) => ReactNode> = {
    help: () => (
      <>
        <dl className={styles.help}>
          {Object.entries(text.help).map(([name, description]) => (
            <div key={name}>
              <dt>{commandButton(name)}</dt>
              <dd>{description}</dd>
            </div>
          ))}
        </dl>
        <p className={styles.muted}>{text.helpFooter}</p>
      </>
    ),
    whoami: () => (data.profile ? `${data.profile.display_name} — ${data.profile.headline}` : 'wariss'),
    about: () => data.profile?.short_bio ?? text.noData,
    skills: () =>
      data.technologies.length ? (
        <p className={styles.tags}>
          {data.technologies.map((technology) => (
            <span key={technology.id}>{technology.name}</span>
          ))}
        </p>
      ) : (
        text.noData
      ),
    projects: () =>
      data.projects.length ? (
        <ul className={styles.list}>
          {data.projects.map((project) => (
            <li key={project.id}>
              <Link to={contentPath(locale, 'projects', project.slug)}>{project.title}</Link>
              <span className={styles.muted}> — {project.summary}</span>
            </li>
          ))}
        </ul>
      ) : (
        text.noData
      ),
    experience: () =>
      data.experiences.length ? (
        <ul className={styles.list}>
          {data.experiences.map((experience) => (
            <li key={experience.id}>
              <span className={styles.accent}>{experience.job_title}</span> @ {experience.organization_name}
              <span className={styles.muted}> ({formatPeriod(experience.started_on, experience.ended_on, locale, text.present)})</span>
            </li>
          ))}
        </ul>
      ) : (
        text.noData
      ),
    contact: () => {
      navigate(pathTo(locale, 'contact'));

      return text.opening('/contact');
    },
    resume: () => {
      window.location.assign(resumeDownloadUrl(locale));

      return text.opening('CV.pdf');
    },
    github: () => {
      if (!github) {
        return text.noData;
      }

      window.open(github.url, '_blank', 'noopener,noreferrer');

      return text.opening(github.url);
    },
    social: () =>
      socialLinks.length ? (
        <ul className={styles.list}>
          {socialLinks.map((link) => (
            <li key={link.id}>
              <span className={styles.accent}>{link.platform}</span>{' '}
              <a href={link.url} target="_blank" rel="noopener noreferrer">
                {link.url}
              </a>
            </li>
          ))}
        </ul>
      ) : (
        text.noData
      ),
    theme: ([value]) => {
      const theme = value === 'light' ? 'light' : value === 'dark' ? 'dark' : document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
      applyTheme(theme);

      return `theme → ${theme}`;
    },
    lang: () => {
      const target = locale === 'fr' ? 'en' : 'fr';
      navigate(`/${target}/terminal`);

      return text.opening(`/${target}/terminal`);
    },
    open: ([page]) => {
      const target = pathTo(locale, page ?? 'home');
      navigate(target);

      return text.opening(target);
    },
    date: () => new Date().toLocaleString(dateLocale),
    echo: (args) => args.join(' '),
    sudo: () => text.sudo,
  };

  function run(raw: string) {
    const line = raw.trim();

    if (line === '') {
      return;
    }

    setHistory((previous) => [line, ...previous].slice(0, 50));
    setCursor(-1);
    setCount((value) => value + 1);
    inputRef.current?.focus();

    const [name = '', ...args] = line.split(/\s+/);
    const key = name.toLowerCase();

    if (key === 'clear') {
      setEntries([]);
      return;
    }

    const entry: Entry = { id: nextId.current++, command: line, index: count, time: clockOf(new Date()) };

    if (key === 'banner') {
      setEntries((previous) => [...previous, entry, { id: nextId.current++, banner: true }]);
      return;
    }

    const handler = commands[key];
    entry.output = handler ? handler(args) : <span className={styles.error}>{text.unknown(name)}</span>;
    setEntries((previous) => [...previous, entry]);
  }

  const onKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
    if (event.key === 'Enter') {
      run(input);
      setInput('');
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      const next = Math.min(cursor + 1, history.length - 1);
      setCursor(next);
      setInput(history[next] ?? input);
    } else if (event.key === 'ArrowDown') {
      event.preventDefault();
      const next = cursor - 1;
      setCursor(Math.max(next, -1));
      setInput(next < 0 ? '' : (history[next] ?? ''));
    } else if (event.key === 'Tab') {
      const match = Object.keys(commands)
        .concat('clear', 'banner')
        .find((name) => input && name.startsWith(input.toLowerCase()));

      if (match) {
        event.preventDefault();
        setInput(match);
      }
    } else if (event.key === 'l' && event.ctrlKey) {
      event.preventDefault();
      setEntries([]);
    }
  };

  const promptHead = (index: number, time: string | undefined) => (
    <span className={styles.promptHead}>
      <span className={styles.host}>[wariss.dev]</span> <span className={styles.branch}>◆main</span>
      <span className={styles.muted}>
        {' '}
        · #{index} · {time ?? '--:--'}
      </span>
    </span>
  );

  const banner = (
    <div className={styles.banner}>
      <div className={styles.logoBox}>
        <pre className={styles.logo} aria-hidden="true">
          {LOGO}
        </pre>
        <p className={styles.logoName}>
          <span className="visually-hidden">WARISS — </span>
          {text.tagline}
        </p>
      </div>

      <div className={styles.intro}>
        <p className={styles.introTitle}>
          {data.profile?.display_name ?? 'WARISS.DEV'} <span className={styles.version}>{VERSION}</span>
        </p>
        {data.profile?.headline ? <p className={styles.headline}>{data.profile.headline}</p> : null}
        <p className={styles.muted}>
          {now ? (
            <>
              <time dateTime={now.toISOString()}>
                {now.toLocaleDateString(dateLocale, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
              </time>{' '}
              · {clockOf(now)}
            </>
          ) : (
            ' '
          )}
        </p>

        <p className={styles.startTitle}>
          {text.start} <span className={styles.muted}>— {text.clickHint}</span>
        </p>
        <ul className={styles.quick}>
          {QUICK_START.map((name) => (
            <li key={name}>
              <span className={styles.chevron} aria-hidden="true">
                ❯
              </span>
              {commandButton(name)}
              <span className={styles.muted}>{text.help[name]}</span>
            </li>
          ))}
        </ul>

        <p className={styles.helpCall}>
          {text.helpBefore} {commandButton('help')} {text.helpAfter} <kbd className={styles.key}>{text.enter} ↵</kbd> {text.helpEnd}
        </p>
        <p className={styles.status}>
          <span className={styles.statusDot} aria-hidden="true">
            ◆
          </span>{' '}
          {text.status(data.projects.length, data.technologies.length)}
        </p>
      </div>
    </div>
  );

  return (
    <div className={styles.terminal} onClick={() => inputRef.current?.focus()} role="presentation">
      <div className={styles.bar} aria-hidden="true">
        <span className={styles.dots}>
          <span />
          <span />
          <span />
        </span>
        <span className={styles.barTitle}>wariss.dev — zsh</span>
        <span className={styles.barClock}>{now ? clockOf(now) : ''}</span>
      </div>
      <div className={styles.screen} ref={screenRef}>
        <div role="log" aria-live="polite" aria-relevant="additions" className={styles.log}>
          {entries.map((entry) =>
            entry.banner ? (
              <div key={entry.id}>{banner}</div>
            ) : (
              <div key={entry.id} className={styles.entry}>
                <p className={styles.promptLines}>
                  {promptHead(entry.index ?? 0, entry.time)}
                  <span>
                    <span className={styles.caret}>◆❯</span> {entry.command}
                  </span>
                </p>
                <div className={styles.output}>{entry.output}</div>
              </div>
            ),
          )}
        </div>

        <div className={styles.inputBlock}>
          {promptHead(count, now ? clockOf(now) : undefined)}
          <div className={styles.inputLine}>
            <label htmlFor="terminal-input" className={styles.caret}>
              ◆❯<span className="visually-hidden"> {text.label}</span>
            </label>
            <input
              id="terminal-input"
              ref={inputRef}
              className={styles.input}
              value={input}
              placeholder={text.placeholder}
              onChange={(event) => setInput(event.target.value)}
              onKeyDown={onKeyDown}
              enterKeyHint="send"
              autoComplete="off"
              autoCapitalize="off"
              autoCorrect="off"
              spellCheck={false}
              autoFocus
            />
          </div>
        </div>
      </div>
      <div className={styles.footer} aria-hidden="true">
        <span>
          <kbd className={styles.key}>↵</kbd> {text.footer.run}
        </span>
        <span>
          <kbd className={styles.key}>↑</kbd>
          <kbd className={styles.key}>↓</kbd> {text.footer.history}
        </span>
        <span>
          <kbd className={styles.key}>Tab</kbd> {text.footer.complete}
        </span>
        <span>
          <kbd className={styles.key}>Ctrl L</kbd> {text.footer.clear}
        </span>
      </div>
    </div>
  );
}
