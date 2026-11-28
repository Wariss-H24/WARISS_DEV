import { motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';

interface RevealProps {
  children: ReactNode;
  /** Décalage en secondes (apparition échelonnée dans une grille). */
  delay?: number;
  className?: string;
  as?: 'div' | 'li' | 'article' | 'section';
}

/**
 * Apparition discrète au défilement (fondu + 12 px), une seule fois. Sans mouvement si le visiteur
 * a demandé à réduire les animations ; le contenu est toujours présent dans le HTML serveur.
 */
export function Reveal({ children, delay = 0, className, as = 'div' }: RevealProps) {
  const reduceMotion = useReducedMotion();
  const Component = motion[as];

  if (reduceMotion) {
    const Static = as;

    return <Static className={className}>{children}</Static>;
  }

  return (
    <Component
      className={className}
      initial={{ opacity: 0, y: 12 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: '0px 0px -8% 0px' }}
      transition={{ duration: 0.6, delay, ease: [0.2, 0.8, 0.2, 1] }}
    >
      {children}
    </Component>
  );
}
