import type { Testimonial } from '@wariss/api-client';
import { Card } from '@wariss/ui';
import { Quote } from 'lucide-react';
import { Reveal } from '~/components/Reveal/Reveal';
import styles from './Testimonials.module.css';

/** Témoignages approuvés : citation, auteur, rôle et entreprise. */
export function TestimonialList({ testimonials }: { testimonials: Testimonial[] }) {
  return (
    <ul className={styles.list}>
      {testimonials.map((testimonial, index) => (
        <Reveal as="li" key={testimonial.id} delay={Math.min(index, 4) * 0.06}>
          <Card padded spotlight className={styles.card}>
            <Quote aria-hidden="true" className={styles.icon} size={22} />
            <blockquote className={styles.quote}>
              <p>{testimonial.quote}</p>
            </blockquote>
            <figure className={styles.author}>
              {testimonial.photo ? (
                <img src={testimonial.photo.url} alt="" width={44} height={44} className={styles.photo} loading="lazy" />
              ) : (
                <span className={styles.initial} aria-hidden="true">
                  {testimonial.author_name.charAt(0)}
                </span>
              )}
              <figcaption>
                <span className={styles.name}>{testimonial.author_name}</span>
                <span className={styles.role}>{[testimonial.author_role, testimonial.author_company].filter(Boolean).join(' · ')}</span>
              </figcaption>
            </figure>
          </Card>
        </Reveal>
      ))}
    </ul>
  );
}
