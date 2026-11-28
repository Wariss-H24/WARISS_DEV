import type { Media } from '@wariss/api-client';
import { Badge, Container } from '@wariss/ui';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { Reveal } from '~/components/Reveal/Reveal';
import styles from './DetailLayout.module.css';

export interface MetaItem {
  label: string;
  value: ReactNode;
}

interface DetailLayoutProps {
  back: { href: string; label: string };
  eyebrow?: ReactNode;
  title: string;
  description?: string | null;
  headerExtra?: ReactNode;
  cover?: Media | null;
  meta?: MetaItem[];
  asideExtra?: ReactNode;
  children: ReactNode;
  after?: ReactNode;
  isPreview?: boolean;
}

/** Bandeau d'aperçu d'un brouillon (lien signé). */
export function PreviewBanner() {
  const { t } = useTranslation();

  return (
    <div className={styles.preview} role="status">
      <Badge tone="warning" dot>
        {t('preview.banner')}
      </Badge>
    </div>
  );
}

/** Gabarit d'une page de détail : en-tête, couverture, contenu, colonne de métadonnées collante. */
export function DetailLayout({ back, eyebrow, title, description, headerExtra, cover, meta = [], asideExtra, children, after, isPreview }: DetailLayoutProps) {
  return (
    <article>
      {isPreview ? <PreviewBanner /> : null}
      <PageHeader
        eyebrow={
          <>
            <Link to={back.href} className={styles.back} prefetch="intent">
              <ArrowLeft aria-hidden="true" size={14} />
              {back.label}
            </Link>
            {eyebrow ? <span aria-hidden="true">/</span> : null}
            {eyebrow}
          </>
        }
        title={title}
        description={description}
      >
        {headerExtra}
      </PageHeader>

      <Container size="wide" className={styles.body}>
        {cover ? (
          <Reveal className={styles.cover}>
            <img
              src={cover.url}
              alt={cover.alt ?? ''}
              width={cover.width ?? undefined}
              height={cover.height ?? undefined}
              decoding="async"
              fetchPriority="high"
            />
          </Reveal>
        ) : null}
        <div className={meta.length || asideExtra ? styles.columns : styles.single}>
          <div className={styles.main}>{children}</div>
          {meta.length || asideExtra ? (
            <aside className={styles.aside}>
              {meta.length ? (
                <dl className={styles.meta}>
                  {meta.map((item) => (
                    <div key={item.label} className={styles.metaItem}>
                      <dt>{item.label}</dt>
                      <dd>{item.value}</dd>
                    </div>
                  ))}
                </dl>
              ) : null}
              {asideExtra}
            </aside>
          ) : null}
        </div>
        {after}
      </Container>
    </article>
  );
}

/** Bloc titré du contenu principal (« Contexte », « Mon rôle »…). */
export function DetailSection({ title, children, id }: { title: string; children: ReactNode; id?: string }) {
  return (
    <section className={styles.section} aria-labelledby={id}>
      <h2 id={id} className={styles.sectionTitle}>
        {title}
      </h2>
      {children}
    </section>
  );
}

/** Galerie d'images publiques. */
export function Gallery({ media, title }: { media?: Media[] | undefined; title: string }) {
  const images = (media ?? []).filter((item) => item.kind === 'image' && item.url);

  if (!images.length) {
    return null;
  }

  return (
    <DetailSection title={title} id="gallery">
      <ul className={styles.gallery}>
        {images.map((image) => (
          <li key={image.id}>
            <figure>
              <img src={image.url} alt={image.alt ?? ''} width={image.width ?? undefined} height={image.height ?? undefined} loading="lazy" decoding="async" />
              {image.caption ? <figcaption>{image.caption}</figcaption> : null}
            </figure>
          </li>
        ))}
      </ul>
    </DetailSection>
  );
}
