import styles from './Logo.module.css';

/** Monogramme et nom du site ; le texte est masqué des lecteurs d'écran par le lien parent. */
export function Logo({ compact = false }: { compact?: boolean }) {
  return (
    <span className={styles.logo} aria-hidden="true">
      <svg className={styles.mark} viewBox="0 0 32 32" width="32" height="32">
        <defs>
          <linearGradient id="wd-logo" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stopColor="var(--color-accent)" />
            <stop offset="100%" stopColor="var(--color-highlight)" />
          </linearGradient>
        </defs>
        <rect x="1" y="1" width="30" height="30" rx="9" fill="none" stroke="url(#wd-logo)" strokeWidth="1.5" />
        <path d="M8 11l3.2 11L16 13l4.8 9L24 11" fill="none" stroke="url(#wd-logo)" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
      {compact ? null : (
        <span className={styles.word}>
          wariss<span className={styles.tld}>.dev</span>
        </span>
      )}
    </span>
  );
}
