import type { CaseStudy, Experiment, Post, Project, Service } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Lock } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { ContentCard } from '~/components/ContentCard/ContentCard';
import { formatDate, formatPrice } from '~/lib/format';
import { contentPath } from '~/lib/routes';

type Level = 'h2' | 'h3';

export function ProjectCard({ project, locale, headingLevel }: { project: Project; locale: Locale; headingLevel?: Level }) {
  const { t } = useTranslation();

  return (
    <ContentCard
      href={contentPath(locale, 'projects', project.slug)}
      title={project.title}
      summary={project.summary}
      cover={project.cover}
      seed={project.id}
      featured={project.is_featured}
      headingLevel={headingLevel}
      technologies={project.technologies}
      eyebrow={
        <>
          {project.category?.name ?? null}
          {project.is_confidential ? (
            <span title={t('meta.confidential')} style={{ display: 'inline-flex' }}>
              <Lock aria-label={t('meta.confidential')} size={12} />
            </span>
          ) : null}
        </>
      }
      meta={project.started_on ? formatDate(project.ended_on ?? project.started_on, locale, 'year') : null}
    />
  );
}

export function CaseStudyCard({ caseStudy, locale, headingLevel }: { caseStudy: CaseStudy; locale: Locale; headingLevel?: Level }) {
  const { t } = useTranslation();

  return (
    <ContentCard
      href={contentPath(locale, 'case-studies', caseStudy.slug)}
      title={caseStudy.title}
      summary={caseStudy.summary}
      cover={caseStudy.cover}
      seed={caseStudy.id + 7}
      headingLevel={headingLevel}
      technologies={caseStudy.technologies}
      eyebrow={t('nav.case-studies')}
      meta={caseStudy.reading_time_minutes ? t('meta.readingTime', { count: caseStudy.reading_time_minutes }) : null}
    />
  );
}

export function ServiceCard({ service, locale, headingLevel }: { service: Service; locale: Locale; headingLevel?: Level }) {
  const { t } = useTranslation();
  const price = formatPrice(service.price_from, service.price_currency, locale);

  return (
    <ContentCard
      href={contentPath(locale, 'services', service.slug)}
      title={service.title}
      summary={service.summary}
      cover={service.cover}
      seed={service.id + 3}
      featured={service.is_featured}
      headingLevel={headingLevel}
      technologies={service.technologies}
      eyebrow={service.category?.name ?? null}
      meta={price ? t('pages.fromPrice', { price }) : service.pricing_model === 'quote' ? t('pages.onQuote') : null}
    />
  );
}

export function ExperimentCard({ experiment, locale, headingLevel }: { experiment: Experiment; locale: Locale; headingLevel?: Level }) {
  return (
    <ContentCard
      href={contentPath(locale, 'lab', experiment.slug)}
      title={experiment.title}
      summary={experiment.summary}
      cover={experiment.cover}
      seed={experiment.id + 11}
      featured={experiment.is_featured}
      headingLevel={headingLevel}
      technologies={experiment.technologies}
      eyebrow={experiment.category?.name ?? experiment.kind.replace('_', ' ')}
      meta={experiment.experimented_on ? formatDate(experiment.experimented_on, locale, 'month') : null}
    />
  );
}

export function PostCard({ post, locale, headingLevel, layout }: { post: Post; locale: Locale; headingLevel?: Level; layout?: 'vertical' | 'horizontal' }) {
  const { t } = useTranslation();

  return (
    <ContentCard
      href={contentPath(locale, 'blog', post.slug)}
      title={post.title}
      summary={post.excerpt}
      cover={post.cover}
      seed={post.id + 5}
      featured={post.is_featured}
      headingLevel={headingLevel}
      layout={layout}
      eyebrow={post.category?.name ?? null}
      meta={
        <>
          <time dateTime={post.published_at}>{formatDate(post.published_at, locale)}</time>
          {post.reading_time_minutes ? <span>· {t('meta.readingTime', { count: post.reading_time_minutes })}</span> : null}
        </>
      }
    />
  );
}
