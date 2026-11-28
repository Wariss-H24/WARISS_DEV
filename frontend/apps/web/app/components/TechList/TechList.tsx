import type { Technology } from '@wariss/api-client';
import { Badge } from '@wariss/ui';
import styles from './TechList.module.css';

/** Liste compacte de technologies (pastilles mono). */
export function TechList({ technologies, max, label }: { technologies?: Technology[] | undefined; max?: number; label?: string }) {
  if (!technologies?.length) {
    return null;
  }

  const shown = max ? technologies.slice(0, max) : technologies;
  const rest = technologies.length - shown.length;

  return (
    <ul className={styles.list} aria-label={label}>
      {shown.map((technology) => (
        <li key={technology.id}>
          <Badge mono>
            {technology.brand_color ? <span className={styles.swatch} style={{ background: technology.brand_color }} aria-hidden="true" /> : null}
            {technology.name}
          </Badge>
        </li>
      ))}
      {rest > 0 ? (
        <li>
          <Badge mono>+{rest}</Badge>
        </li>
      ) : null}
    </ul>
  );
}
