import type { ReactNode } from 'react';
import { Reveal } from '~/components/Reveal/Reveal';
import styles from './CardGrid.module.css';

/** Grille responsive de cartes, apparition échelonnée. */
export function CardGrid({ children, columns = 3 }: { children: ReactNode[]; columns?: 2 | 3 }) {
  return (
    <ul className={columns === 2 ? styles.two : styles.three}>
      {children.map((child, index) => (
        <Reveal as="li" key={index} delay={Math.min(index, 5) * 0.06} className={styles.item}>
          {child}
        </Reveal>
      ))}
    </ul>
  );
}
