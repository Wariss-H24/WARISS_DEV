import type { CaseStudy, Experiment, Post, Project, Service } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Badge, Button, Card } from '@wariss/ui';
import { CircleCheck, ExternalLink, FileText, Play, Sparkles } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { Markdown } from '~/components/Markdown/Markdown';
import { ShareButtons } from '~/components/ShareButtons/ShareButtons';
import { SocialIcon } from '~/components/SocialIcon/SocialIcon';
import { TechList } from '~/components/TechList/TechList';
import { formatDate, formatPeriod, formatPrice } from '~/lib/format';
import { contentPath, pathTo } from '~/lib/routes';
import { trackEvent } from '~/lib/tracking';
import { CaseStudyCard } from './cards';
import { CardGrid } from './CardGrid';
import { DetailLayout, DetailSection, Gallery, type MetaItem } from './DetailLayout';
import styles from './views.module.css';

function OutboundButton({
  href,
  label,
  icon,
  variant = 'secondary',
  content,
}: {
  href: string | null;
  label: string;
  icon: React.ReactNode;
  variant?: 'primary' | 'secondary';
  content?: { type: 'project' | 'experiment'; id: number };
}) {
  if (!href) {
    return null;
  }

  return (
    <Button asChild variant={variant}>
      <a href={href} target="_blank" rel="noopener noreferrer" onClick={() => trackEvent('outbound_click', { target: href, content: content ?? null })}>
        {icon}
        {label}
      </a>
    </Button>
  );
}

export function ProjectView({ project, locale }: { project: Project; locale: Locale }) {
  const { t } = useTranslation();
  const tracking = { type: 'project' as const, id: project.id };
  const meta: MetaItem[] = [
    ...(project.category ? [{ label: t('pages.type'), value: project.category.name }] : []),
    ...(project.started_on ? [{ label: t('pages.period'), value: formatPeriod(project.started_on, project.ended_on, locale, t('meta.present')) }] : []),
    ...(project.client_name ? [{ label: t('pages.client'), value: project.client_name }] : []),
    ...(project.is_confidential ? [{ label: t('pages.client'), value: t('meta.confidential') }] : []),
  ];

  return (
    <DetailLayout
      isPreview={project.is_preview}
      back={{ href: pathTo(locale, 'projects'), label: t('nav.projects') }}
      eyebrow={project.category?.name}
      title={project.title}
      description={project.summary}
      cover={project.cover}
      headerExtra={
        <>
          <OutboundButton
            href={project.live_url}
            label={t('actions.liveSite')}
            icon={<ExternalLink aria-hidden="true" />}
            variant="primary"
            content={tracking}
          />
          <OutboundButton href={project.demo_url} label={t('actions.demo')} icon={<Play aria-hidden="true" />} content={tracking} />
          <OutboundButton href={project.repository_url} label={t('actions.sourceCode')} icon={<SocialIcon platform="github" size={16} />} content={tracking} />
        </>
      }
      meta={meta}
      asideExtra={
        <>
          {project.technologies?.length ? (
            <div className={styles.asideBlock}>
              <p className="mono-label">{t('pages.stack')}</p>
              <TechList technologies={project.technologies} />
            </div>
          ) : null}
          <ShareButtons title={project.title} content={tracking} label={t('actions.share')} />
        </>
      }
      after={
        project.case_studies?.length ? (
          <section className={styles.related} aria-labelledby="related-case-studies">
            <h2 id="related-case-studies" className={styles.relatedTitle}>
              {t('pages.relatedCaseStudies')}
            </h2>
            <CardGrid columns={2}>
              {project.case_studies.map((caseStudy) => (
                <CaseStudyCard key={caseStudy.id} caseStudy={{ ...caseStudy, technologies: caseStudy.technologies ?? [] }} locale={locale} />
              ))}
            </CardGrid>
          </section>
        ) : null
      }
    >
      <DetailSection title={t('pages.overview')} id="overview">
        <Markdown>{project.description}</Markdown>
      </DetailSection>
      {project.context ? (
        <DetailSection title={t('pages.context')} id="context">
          <Markdown>{project.context}</Markdown>
        </DetailSection>
      ) : null}
      <DetailSection title={t('pages.role')} id="role">
        <Markdown>{project.role}</Markdown>
        {project.responsibilities ? <Markdown>{project.responsibilities}</Markdown> : null}
      </DetailSection>
      {project.features?.length ? (
        <DetailSection title={t('pages.features')} id="features">
          <ul className={styles.features}>
            {project.features.map((feature) => (
              <li key={feature.id}>
                <Card padded className={styles.feature}>
                  <CircleCheck aria-hidden="true" size={20} className={styles.featureIcon} />
                  <div>
                    <h3 className={styles.featureTitle}>{feature.title}</h3>
                    {feature.description ? <p className={styles.featureText}>{feature.description}</p> : null}
                  </div>
                </Card>
              </li>
            ))}
          </ul>
        </DetailSection>
      ) : null}
      <Gallery media={project.gallery} title={t('pages.gallery')} />
    </DetailLayout>
  );
}

export function CaseStudyView({ caseStudy, locale }: { caseStudy: CaseStudy; locale: Locale }) {
  const { t } = useTranslation();
  const meta: MetaItem[] = [
    ...(caseStudy.project
      ? [{ label: t('nav.projects'), value: <Link to={contentPath(locale, 'projects', caseStudy.project.slug)}>{caseStudy.project.title}</Link> }]
      : []),
    ...(caseStudy.started_on ? [{ label: t('pages.period'), value: formatPeriod(caseStudy.started_on, caseStudy.ended_on, locale, t('meta.present')) }] : []),
    ...(caseStudy.reading_time_minutes ? [{ label: t('pages.type'), value: t('meta.readingTime', { count: caseStudy.reading_time_minutes }) }] : []),
  ];
  const sections = caseStudy.sections ?? [];

  return (
    <DetailLayout
      isPreview={caseStudy.is_preview}
      back={{ href: pathTo(locale, 'projects'), label: t('nav.projects') }}
      eyebrow={t('nav.case-studies')}
      title={caseStudy.title}
      description={caseStudy.summary}
      cover={caseStudy.cover}
      meta={meta}
      asideExtra={
        <>
          {sections.length > 2 ? (
            <nav aria-label={t('pages.tableOfContents')} className={styles.toc}>
              <p className="mono-label">{t('pages.tableOfContents')}</p>
              <ol>
                {sections.map((section) => (
                  <li key={section.id}>
                    <a href={`#section-${section.id}`}>{section.title ?? section.type}</a>
                  </li>
                ))}
              </ol>
            </nav>
          ) : null}
          {caseStudy.technologies?.length ? (
            <div className={styles.asideBlock}>
              <p className="mono-label">{t('pages.stack')}</p>
              <TechList technologies={caseStudy.technologies} />
            </div>
          ) : null}
          <ShareButtons title={caseStudy.title} content={{ type: 'case_study', id: caseStudy.id }} label={t('actions.share')} />
        </>
      }
    >
      {sections.map((section) => (
        <DetailSection key={section.id} title={section.title ?? section.type} id={`section-${section.id}`}>
          <Markdown>{section.body}</Markdown>
        </DetailSection>
      ))}
      <Gallery media={caseStudy.gallery} title={t('pages.gallery')} />
    </DetailLayout>
  );
}

export function ServiceView({ service, locale }: { service: Service; locale: Locale }) {
  const { t } = useTranslation();
  const price = formatPrice(service.price_from, service.price_currency, locale);
  const delivery =
    service.delivery_min_days && service.delivery_max_days && service.delivery_min_days !== service.delivery_max_days
      ? t('pages.deliveryDays', { min: service.delivery_min_days, max: service.delivery_max_days })
      : service.delivery_min_days || service.delivery_max_days
        ? t('pages.deliveryDaysSingle', { count: service.delivery_min_days ?? service.delivery_max_days ?? 0 })
        : null;
  const meta: MetaItem[] = [
    ...(price
      ? [{ label: t('pages.pricing'), value: t('pages.fromPrice', { price }) }]
      : service.pricing_model === 'quote'
        ? [{ label: t('pages.pricing'), value: t('pages.onQuote') }]
        : []),
    ...(delivery ? [{ label: t('pages.delivery'), value: delivery }] : []),
    ...(service.target_audience ? [{ label: t('pages.audience'), value: service.target_audience }] : []),
  ];
  const features = service.features ?? [];
  const groups: Array<['included' | 'benefit' | 'deliverable', string]> = [
    ['included', t('pages.included')],
    ['benefit', t('pages.benefits')],
    ['deliverable', t('pages.deliverables')],
  ];

  return (
    <DetailLayout
      isPreview={service.is_preview}
      back={{ href: pathTo(locale, 'services'), label: t('nav.services') }}
      eyebrow={service.category?.name}
      title={service.title}
      description={service.summary}
      cover={service.cover}
      headerExtra={
        <Button asChild variant="gradient" size="lg">
          <Link
            to={`${pathTo(locale, 'contact')}?type=quote&service=${service.slug}`}
            onClick={() => trackEvent('cta_click', { target: 'service-quote', content: { type: 'service', id: service.id } })}
          >
            <Sparkles aria-hidden="true" />
            {service.cta_label ?? t('pages.requestQuote')}
          </Link>
        </Button>
      }
      meta={meta}
      asideExtra={
        service.technologies?.length ? (
          <div className={styles.asideBlock}>
            <p className="mono-label">{t('pages.stack')}</p>
            <TechList technologies={service.technologies} />
          </div>
        ) : null
      }
    >
      <DetailSection title={t('pages.overview')} id="overview">
        <Markdown>{service.description}</Markdown>
      </DetailSection>
      {groups.map(([kind, title]) => {
        const items = features.filter((feature) => feature.kind === kind);

        return items.length ? (
          <DetailSection key={kind} title={title} id={kind}>
            <ul className={styles.checklist}>
              {items.map((feature) => (
                <li key={feature.id}>
                  <CircleCheck aria-hidden="true" size={18} />
                  <span>
                    <strong>{feature.label}</strong>
                    {feature.description ? <span className={styles.checkText}>{feature.description}</span> : null}
                  </span>
                </li>
              ))}
            </ul>
          </DetailSection>
        ) : null;
      })}
      <Gallery media={service.gallery} title={t('pages.gallery')} />
    </DetailLayout>
  );
}

export function ExperimentView({ experiment, locale }: { experiment: Experiment; locale: Locale }) {
  const { t } = useTranslation();
  const tracking = { type: 'experiment' as const, id: experiment.id };

  return (
    <DetailLayout
      isPreview={experiment.is_preview}
      back={{ href: pathTo(locale, 'lab'), label: t('nav.lab') }}
      eyebrow={experiment.category?.name}
      title={experiment.title}
      description={experiment.summary}
      cover={experiment.cover}
      headerExtra={
        <>
          {experiment.demo_url ? (
            <Button asChild variant="gradient">
              <a
                href={experiment.demo_url}
                target="_blank"
                rel="noopener noreferrer"
                onClick={() => trackEvent('demo_open', { target: experiment.demo_url ?? undefined, content: tracking })}
              >
                <Play aria-hidden="true" />
                {t('pages.openDemo')}
              </a>
            </Button>
          ) : null}
          <OutboundButton href={experiment.video_url} label={t('pages.watchVideo')} icon={<Play aria-hidden="true" />} content={tracking} />
          <OutboundButton
            href={experiment.repository_url}
            label={t('actions.sourceCode')}
            icon={<SocialIcon platform="github" size={16} />}
            content={tracking}
          />
        </>
      }
      meta={[
        { label: t('pages.type'), value: <Badge mono>{experiment.kind.replace('_', ' ')}</Badge> },
        ...(experiment.experimented_on ? [{ label: t('pages.period'), value: formatDate(experiment.experimented_on, locale, 'month') }] : []),
      ]}
      asideExtra={
        experiment.technologies?.length ? (
          <div className={styles.asideBlock}>
            <p className="mono-label">{t('pages.stack')}</p>
            <TechList technologies={experiment.technologies} />
          </div>
        ) : null
      }
    >
      {experiment.objective ? (
        <DetailSection title={t('pages.objective')} id="objective">
          <Markdown>{experiment.objective}</Markdown>
        </DetailSection>
      ) : null}
      <DetailSection title={t('pages.overview')} id="overview">
        <Markdown>{experiment.description}</Markdown>
      </DetailSection>
      {experiment.technical_notes ? (
        <DetailSection title={t('pages.technicalNotes')} id="notes">
          <Markdown>{experiment.technical_notes}</Markdown>
        </DetailSection>
      ) : null}
      <Gallery media={experiment.gallery} title={t('pages.gallery')} />
    </DetailLayout>
  );
}

export function PostView({ post, locale }: { post: Post; locale: Locale }) {
  const { t } = useTranslation();

  return (
    <DetailLayout
      isPreview={post.is_preview}
      back={{ href: pathTo(locale, 'blog'), label: t('nav.blog') }}
      eyebrow={post.category ? <Link to={`${pathTo(locale, 'blog')}/category/${post.category.slug}`}>{post.category.name}</Link> : null}
      title={post.title}
      description={post.excerpt}
      headerExtra={
        <p className={styles.postMeta}>
          <FileText aria-hidden="true" size={14} />
          <time dateTime={post.published_at}>{t('meta.publishedOn', { date: formatDate(post.published_at, locale) })}</time>
          {post.reading_time_minutes ? <span>· {t('meta.readingTime', { count: post.reading_time_minutes })}</span> : null}
        </p>
      }
      cover={post.cover}
      asideExtra={
        <>
          {post.tags?.length ? (
            <div className={styles.asideBlock}>
              <p className="mono-label">{t('pages.tags')}</p>
              <ul className={styles.tags}>
                {post.tags.map((tag) => (
                  <li key={tag.id}>
                    <Link to={`${pathTo(locale, 'blog')}/tag/${tag.slug}`} className={styles.tag}>
                      #{tag.name}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ) : null}
          <ShareButtons title={post.title} content={{ type: 'post', id: post.id }} label={t('pages.shareThis')} />
        </>
      }
    >
      <Markdown className="prose">{post.content}</Markdown>
      <Gallery media={post.gallery} title={t('pages.gallery')} />
    </DetailLayout>
  );
}
