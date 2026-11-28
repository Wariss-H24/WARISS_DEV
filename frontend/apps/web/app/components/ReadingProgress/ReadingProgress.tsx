import { motion, useScroll, useSpring } from 'motion/react';
import styles from './ReadingProgress.module.css';

/** Barre de progression de lecture en haut de page (décorative). */
export function ReadingProgress() {
  const { scrollYProgress } = useScroll();
  const scaleX = useSpring(scrollYProgress, { stiffness: 140, damping: 30, restDelta: 0.001 });

  return <motion.div aria-hidden="true" className={styles.bar} style={{ scaleX }} />;
}
