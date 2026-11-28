import type { Experience, Profile, Project, Technology } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { useEffect, useRef, useState, type KeyboardEvent, type ReactNode } from 'react';
import { Link, useNavigate } from 'react-router';
import { applyTheme } from '~/components/ThemeToggle/ThemeToggle';
import { formatPeriod } from '~/lib/format';
import { contentPath, pathTo } from '~/lib/routes';
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
  command?: string;
  output: ReactNode;
}

const TEXT = {
  fr: {
    welcome: 'Bienvenue sur le terminal de WARISS.DEV. Tapez « help » pour la liste des commandes.',
    unknown: (command: string) => `Commande inconnue : ${command}. Tapez « help ».`,
    label: 'Saisir une commande',
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
      clear: 'Effacer l’écran',
    },
    noData: 'Aucune donnée pour le moment.',
    opening: (target: string) => `Ouverture de ${target}…`,
  },
  en: {
    welcome: 'Welcome to the WARISS.DEV terminal. Type “help” for the list of commands.',
    unknown: (command: string) => `Unknown command: ${command}. Type “help”.`,
    label: 'Type a command',
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
      clear: 'Clear the screen',
    },
    noData: 'No data yet.',
    opening: (target: string) => `Opening ${target}…`,
  },
} as const;

/**
 * Terminal interactif (cahier des charges §21.2 ter) : simulation dans le navigateur, construite à
 * partir des données publiques de l'API. Aucune commande système n'est exécutée. Historique avec
 * les flèches, complétion avec Tab, journal annoncé aux lecteurs d'écran.
 */
export function Terminal({ locale, data }: { locale: Locale; data: TerminalData }) {
  const text = TEXT[locale];
  const navigate = useNavigate();
  const [entries, setEntries] = useState<Entry[]>([{ id: 0, output: text.welcome }]);
  const [input, setInput] = useState('');
  const [history, setHistory] = useState<string[]>([]);
  const [cursor, setCursor] = useState(-1);
  const inputRef = useRef<HTMLInputElement>(null);
  const endRef = useRef<HTMLDivElement>(null);
  const nextId = useRef(1);
  const prompt = `${(data.profile?.first_name ?? 'guest').toLowerCase()}@wariss.dev:~$`;
  const github = data.profile?.social_links.find((link) => link.platform.toLowerCase() === 'github');

  useEffect(() => {
    endRef.current?.scrollIntoView({ block: 'nearest' });
  }, [entries]);

  const commands: Record<string, (args: string[]) => ReactNode> = {
    help: () => (
      <dl className={styles.help}>
        {Object.entries(text.help).map(([name, description]) => (
          <div key={name}>
            <dt>{name}</dt>
            <dd>{description}</dd>
          </div>
        ))}
      </dl>
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
              <span className={styles.muted}>
                {' '}
                ({formatPeriod(experience.started_on, experience.ended_on, locale, locale === 'fr' ? 'aujourd’hui' : 'present')})
              </span>
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
      data.profile?.social_links.length ? (
        <ul className={styles.list}>
          {data.profile.social_links.map((link) => (
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
    date: () => new Date().toLocaleString(locale === 'fr' ? 'fr-FR' : 'en-GB'),
    echo: (args) => args.join(' '),
    sudo: () => (locale === 'fr' ? 'Bien essayé. 😄' : 'Nice try. 😄'),
  };

  const run = (raw: string) => {
    const line = raw.trim();

    if (line === '') {
      return;
    }

    setHistory((previous) => [line, ...previous].slice(0, 50));
    setCursor(-1);

    const [name = '', ...args] = line.split(/\s+/);

    if (name === 'clear') {
      setEntries([]);
      return;
    }

    const handler = commands[name.toLowerCase()];
    const output = handler ? handler(args) : <span className={styles.error}>{text.unknown(name)}</span>;
    setEntries((previous) => [...previous, { id: nextId.current++, command: line, output }]);
  };

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
        .concat('clear')
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

  return (
    <div className={styles.terminal} onClick={() => inputRef.current?.focus()} role="presentation">
      <div className={styles.bar} aria-hidden="true">
        <span className={styles.dots}>
          <span />
          <span />
          <span />
        </span>
        <span>wariss.dev — zsh</span>
      </div>
      <div className={styles.screen}>
        <div role="log" aria-live="polite" aria-relevant="additions" className={styles.log}>
          {entries.map((entry) => (
            <div key={entry.id} className={styles.entry}>
              {entry.command ? (
                <p>
                  <span className={styles.prompt}>{prompt}</span> {entry.command}
                </p>
              ) : null}
              <div className={styles.output}>{entry.output}</div>
            </div>
          ))}
        </div>
        <div className={styles.inputLine} ref={endRef}>
          <label htmlFor="terminal-input" className={styles.prompt}>
            {prompt}
            <span className="visually-hidden"> {text.label}</span>
          </label>
          <input
            id="terminal-input"
            ref={inputRef}
            className={styles.input}
            value={input}
            onChange={(event) => setInput(event.target.value)}
            onKeyDown={onKeyDown}
            autoComplete="off"
            autoCapitalize="off"
            autoCorrect="off"
            spellCheck={false}
            autoFocus
          />
        </div>
      </div>
    </div>
  );
}
