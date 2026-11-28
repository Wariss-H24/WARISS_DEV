import type { Technology } from '@wariss/api-client';
import { useTranslation } from 'react-i18next';
import styles from './TechMarquee.module.css';

/**
 * Bandeau de technologies qui défile en continu (pause au survol, arrêt si le visiteur réduit les
 * animations). La liste est doublée pour une boucle sans saut ; la copie est masquée des lecteurs d'écran.
 */
export function TechMarquee({ technologies }: { technologies: Technology[] }) {
  const { t } = useTranslation();

  if (technologies.length < 4) {
    return null;
  }

  const row = (hidden: boolean) => (
    <ul className={styles.row} aria-hidden={hidden || undefined}>
      {technologies.map((technology) => (
        <li key={technology.id} className={styles.item}>
          {technology.icon ? (
            <img src={technology.icon.url} alt="" width={20} height={20} loading="lazy" />
          ) : (
            <span className={styles.dot} style={technology.brand_color ? { background: technology.brand_color } : undefined} />
          )}
          {technology.name}
        </li>
      ))}
    </ul>
  );

  return (
    <section className={styles.marquee} aria-label={t('home.stackEyebrow')}>
      <div className={styles.track}>
        {row(false)}
        {row(true)}
      </div>
    </section>
  );
}
