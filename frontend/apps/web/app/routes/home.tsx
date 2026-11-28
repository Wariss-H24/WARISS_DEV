import type { Page, Paginated, Post, Project, Service, TechnologyCategory, Testimonial } from '@wariss/api-client';
import { Container, Section, SectionHeading } from '@wariss/ui';
import { useTranslation } from 'react-i18next';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/home';
import { CardGrid } from '~/features/content/CardGrid';
import { PostCard, ProjectCard, ServiceCard } from '~/features/content/cards';
import { Bento } from '~/features/home/Bento';
import { CtaBanner } from '~/features/home/CtaBanner';
import { Hero } from '~/features/home/Hero';
import { TechMarquee } from '~/features/home/TechMarquee';
import { TestimonialList } from '~/features/home/Testimonials';
import { SectionRenderer, type SectionData } from '~/features/sections/SectionRenderer';
import { apiGetOr } from '~/lib/api.server';
import { routeLocale, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';

const emptyPage = { data: [], meta: { current_page: 1, last_page: 1, per_page: 0, total: 0 } };

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);

  const [page, featured, services, posts, testimonials, technologies] = await Promise.all([
    apiGetOr<{ data: Page } | null>('/pages/home', locale, null),
    apiGetOr<Paginated<Project>>('/projects', locale, emptyPage, { per_page: 6 }),
    apiGetOr<Paginated<Service>>('/services', locale, emptyPage, { per_page: 3 }),
    apiGetOr<Paginated<Post>>('/posts', locale, emptyPage, { per_page: 3 }),
    apiGetOr<{ data: Testimonial[] }>('/testimonials', locale, { data: [] }),
    apiGetOr<{ data: TechnologyCategory[] }>('/technologies', locale, { data: [] }),
  ]);

  const allTechnologies = technologies.data.flatMap((category) => category.technologies);
  const highlighted = allTechnologies.filter((technology) => technology.is_featured);

  return {
    locale,
    page: page?.data ?? null,
    projects: featured.data,
    projectsTotal: featured.meta.total,
    services: services.data,
    posts: posts.data,
    postsTotal: posts.meta.total,
    testimonials: testimonials.data,
    technologies: highlighted.length >= 4 ? highlighted : allTechnologies,
    technologiesTotal: allTechnologies.length,
    tracking: page?.data ? { type: 'page' as const, id: page.data.id } : undefined,
  };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const layout = matches.find((match) => match?.id === 'routes/lang-layout')?.loaderData as Awaited<ReturnType<typeof layoutLoader>> | undefined;
  const profile = layout?.site?.profile;
  const locale = loaderData?.locale ?? 'fr';
  const siteName = (layout?.site?.settings.general?.site_name as string | undefined) ?? 'WARISS.DEV';

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName,
    locale,
    title: profile ? `${profile.display_name} · ${profile.headline}` : siteName,
    description: profile?.short_bio ?? (layout?.site?.settings.general?.description as string | undefined),
    paths: staticPaths('home'),
    image: profile?.cover ?? profile?.avatar ?? null,
    type: 'profile',
    seo: loaderData?.page?.seo ?? null,
  });
}

export default function Home() {
  const data = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const { t } = useTranslation();
  const { locale } = data;
  const profile = layout?.site?.profile ?? null;
  const sectionData: SectionData = { profile, ...data };
  const sections = data.page?.sections ?? [];

  // Accueil composé dans l'administration (pages et sections, D18).
  if (sections.length) {
    return (
      <>
        {sections.map((section) => (
          <SectionRenderer key={section.id} section={section} locale={locale} data={sectionData} />
        ))}
      </>
    );
  }

  // Composition par défaut, tant qu'aucune section n'est configurée.
  return (
    <>
      <Hero locale={locale} profile={profile} technologies={data.technologies} />
      <TechMarquee technologies={data.technologies} />
      <Bento
        locale={locale}
        profile={profile}
        stats={{ projects: data.projectsTotal, technologies: data.technologiesTotal, posts: data.postsTotal }}
        latestPost={data.posts[0] ?? null}
      />

      {data.projects.length ? (
        <Section aria-labelledby="featured-title">
          <Container size="wide">
            <SectionHeading
              id="featured-title"
              eyebrow={t('home.featuredEyebrow')}
              title={t('home.featuredTitle')}
              description={t('home.featuredDescription')}
            />
            <CardGrid>
              {data.projects.map((project) => (
                <ProjectCard key={project.id} project={project} locale={locale} />
              ))}
            </CardGrid>
          </Container>
        </Section>
      ) : null}

      {data.services.length ? (
        <Section alt aria-labelledby="services-title">
          <Container size="wide">
            <SectionHeading
              id="services-title"
              eyebrow={t('home.servicesEyebrow')}
              title={t('home.servicesTitle')}
              description={t('home.servicesDescription')}
            />
            <CardGrid>
              {data.services.map((service) => (
                <ServiceCard key={service.id} service={service} locale={locale} />
              ))}
            </CardGrid>
          </Container>
        </Section>
      ) : null}

      {data.testimonials.length ? (
        <Section aria-labelledby="testimonials-title">
          <Container size="wide">
            <SectionHeading id="testimonials-title" eyebrow={t('home.testimonialsEyebrow')} title={t('home.testimonialsTitle')} />
            <TestimonialList testimonials={data.testimonials.slice(0, 6)} />
          </Container>
        </Section>
      ) : null}

      {data.posts.length ? (
        <Section aria-labelledby="posts-title">
          <Container size="wide">
            <SectionHeading id="posts-title" eyebrow={t('home.postsEyebrow')} title={t('home.postsTitle')} description={t('home.postsDescription')} />
            <CardGrid>
              {data.posts.map((post) => (
                <PostCard key={post.id} post={post} locale={locale} />
              ))}
            </CardGrid>
          </Container>
        </Section>
      ) : null}

      <CtaBanner locale={locale} />
    </>
  );
}
