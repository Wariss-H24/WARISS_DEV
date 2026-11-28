import type { PageSection, Post, Profile, Project, Service, Technology, Testimonial } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Button, Container, Section, SectionHeading } from '@wariss/ui';
import { ArrowRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { Markdown } from '~/components/Markdown/Markdown';
import { pathTo, type RouteKey } from '~/lib/routes';
import { CardGrid } from '../content/CardGrid';
import { PostCard, ProjectCard, ServiceCard } from '../content/cards';
import { CtaBanner } from '../home/CtaBanner';
import { Hero } from '../home/Hero';
import { TestimonialList } from '../home/Testimonials';

/** Données disponibles pour les sections (chargées par la page). */
export interface SectionData {
  profile: Profile | null;
  projects: Project[];
  services: Service[];
  posts: Post[];
  testimonials: Testimonial[];
  technologies: Technology[];
}

function itemsOf<T>(section: PageSection, type: string): T[] {
  return section.items.filter((item) => item.itemable_type === type).map((item) => item.itemable as unknown as T);
}

function ViewAll({ locale, to, label }: { locale: Locale; to: RouteKey; label: string }) {
  return (
    <div style={{ marginTop: 'var(--space-10)', display: 'flex', justifyContent: 'center' }}>
      <Button asChild variant="secondary">
        <Link to={pathTo(locale, to)} prefetch="intent">
          {label}
          <ArrowRight aria-hidden="true" />
        </Link>
      </Button>
    </div>
  );
}

function Block({ section, eyebrow, children, alt = false }: { section: PageSection; eyebrow?: string; children: ReactNode; alt?: boolean }) {
  return (
    <Section alt={alt} aria-labelledby={`section-${section.id}`}>
      <Container size="wide">
        {section.title ? <SectionHeading id={`section-${section.id}`} eyebrow={eyebrow} title={section.title} description={section.subtitle} /> : null}
        {children}
      </Container>
    </Section>
  );
}

/**
 * Rend une section de page gérée dans l'administration (D18) : chaque type correspond à un composant.
 * Les contenus mis en avant viennent de la section ; à défaut, des dernières publications.
 */
export function SectionRenderer({ section, locale, data }: { section: PageSection; locale: Locale; data: SectionData }) {
  const { t } = useTranslation();
  const limit = typeof section.config.limit === 'number' ? section.config.limit : 6;

  switch (section.type) {
    case 'hero':
      return <Hero locale={locale} profile={data.profile} technologies={data.technologies} title={section.title} text={section.subtitle ?? section.body} />;

    case 'featured_projects': {
      const projects = itemsOf<Project>(section, 'project');
      const list = (projects.length ? projects : data.projects).slice(0, limit);

      return list.length ? (
        <Block section={section} eyebrow={t('home.featuredEyebrow')}>
          <CardGrid>
            {list.map((project) => (
              <ProjectCard key={project.id} project={project} locale={locale} />
            ))}
          </CardGrid>
          <ViewAll locale={locale} to="projects" label={t('actions.viewAll')} />
        </Block>
      ) : null;
    }

    case 'services': {
      const services = itemsOf<Service>(section, 'service');
      const list = (services.length ? services : data.services).slice(0, limit);

      return list.length ? (
        <Block section={section} eyebrow={t('home.servicesEyebrow')} alt>
          <CardGrid>
            {list.map((service) => (
              <ServiceCard key={service.id} service={service} locale={locale} />
            ))}
          </CardGrid>
          <ViewAll locale={locale} to="services" label={t('actions.viewAll')} />
        </Block>
      ) : null;
    }

    case 'latest_posts': {
      const posts = itemsOf<Post>(section, 'post');
      const list = (posts.length ? posts : data.posts).slice(0, Math.min(limit, 6));

      return list.length ? (
        <Block section={section} eyebrow={t('home.postsEyebrow')}>
          <CardGrid>
            {list.map((post) => (
              <PostCard key={post.id} post={post} locale={locale} />
            ))}
          </CardGrid>
          <ViewAll locale={locale} to="blog" label={t('actions.viewAll')} />
        </Block>
      ) : null;
    }

    case 'cta':
    case 'contact':
      return <CtaBanner locale={locale} title={section.title} text={section.subtitle ?? section.body} label={section.cta_label} href={section.cta_url} />;

    case 'about':
    case 'experiences':
    case 'skills':
    case 'richtext':
    case 'faq':
    case 'gallery':
    default:
      return (
        <Block section={section}>
          <Markdown>{section.body}</Markdown>
          {section.cta_label && section.cta_url ? (
            <div style={{ marginTop: 'var(--space-8)' }}>
              <Button asChild variant="secondary">
                <a href={section.cta_url}>
                  {section.cta_label}
                  <ArrowRight aria-hidden="true" />
                </a>
              </Button>
            </div>
          ) : null}
          {section.type === 'experiences' ? <ViewAll locale={locale} to="experience" label={t('nav.experience')} /> : null}
          {section.type === 'skills' ? <ViewAll locale={locale} to="skills" label={t('nav.skills')} /> : null}
          {section.type === 'about' && data.testimonials.length ? (
            <div style={{ marginTop: 'var(--space-12)' }}>
              <TestimonialList testimonials={data.testimonials.slice(0, 3)} />
            </div>
          ) : null}
        </Block>
      );
  }
}
