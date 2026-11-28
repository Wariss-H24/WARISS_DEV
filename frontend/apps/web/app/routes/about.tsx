import type { Page, Testimonial } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Badge, Button, Card, Container, Section } from '@wariss/ui';
import { ArrowRight, FileDown, Globe, MapPin } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link, useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/about';
import { Markdown } from '~/components/Markdown/Markdown';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { SocialIcon } from '~/components/SocialIcon/SocialIcon';
import { CtaBanner } from '~/features/home/CtaBanner';
import { TestimonialList } from '~/features/home/Testimonials';
import { SectionRenderer } from '~/features/sections/SectionRenderer';
import { apiGetOr } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { pathTo } from '~/lib/routes';
import { buildMeta, staticPaths } from '~/lib/seo';
import { resumeDownloadUrl } from '~/lib/tracking';
import type { loader as layoutLoader } from './lang-layout';
import styles from './about.module.css';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const [page, testimonials] = await Promise.all([
    apiGetOr<{ data: Page } | null>('/pages/about', locale, null),
    apiGetOr<{ data: Testimonial[] }>('/testimonials', locale, { data: [] }),
  ]);

  return { locale, page: page?.data ?? null, testimonials: testimonials.data, tracking: page?.data ? { type: 'page' as const, id: page.data.id } : undefined };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const site = layoutSite(matches);
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;
  const profile = site?.profile;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(site),
    locale,
    title: loaderData?.page?.title ?? (profile ? `${t('nav.about')} · ${profile.display_name}` : t('nav.about')),
    description: loaderData?.page?.summary ?? profile?.short_bio,
    paths: staticPaths('about'),
    image: profile?.avatar ?? null,
    type: 'profile',
    seo: loaderData?.page?.seo ?? null,
  });
}

export default function About() {
  const { locale, page, testimonials } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const { t } = useTranslation();
  const profile = layout?.site?.profile ?? null;
  const languageNames = new Intl.DisplayNames([locale], { type: 'language' });

  return (
    <>
      <PageHeader
        eyebrow={t('pages.aboutEyebrow')}
        title={page?.title ?? profile?.display_name ?? t('nav.about')}
        description={page?.summary ?? profile?.headline}
        aside={
          profile?.avatar ? (
            <div className={styles.portrait}>
              <img
                src={profile.avatar.url}
                alt={profile.avatar.alt ?? profile.display_name}
                width={profile.avatar.width ?? 480}
                height={profile.avatar.height ?? 480}
              />
            </div>
          ) : null
        }
      >
        <Button asChild variant="gradient">
          <Link to={pathTo(locale, 'contact')}>
            {t('actions.contactMe')}
            <ArrowRight aria-hidden="true" />
          </Link>
        </Button>
        <Button asChild variant="secondary">
          <a href={resumeDownloadUrl(locale)} download>
            <FileDown aria-hidden="true" />
            {t('actions.downloadResume')}
          </a>
        </Button>
      </PageHeader>

      <Section>
        <Container size="wide" className={styles.layout}>
          <div className={styles.bio}>
            <Markdown>{profile?.biography}</Markdown>
          </div>
          {profile ? (
            <aside>
              <Card padded className={styles.facts}>
                <Badge tone={profile.availability_status === 'available' ? 'success' : 'warning'} dot="pulse">
                  {t(`availability.${profile.availability_status}`)}
                </Badge>
                {profile.availability_note ? <p className={styles.note}>{profile.availability_note}</p> : null}
                {profile.location_label ? (
                  <p className={styles.fact}>
                    <MapPin aria-hidden="true" size={16} />
                    {profile.location_label}
                  </p>
                ) : null}
                {profile.spoken_languages.length ? (
                  <p className={styles.fact}>
                    <Globe aria-hidden="true" size={16} />
                    {profile.spoken_languages.map((language) => languageNames.of(language.code) ?? language.code).join(' · ')}
                  </p>
                ) : null}
                {profile.social_links.length ? (
                  <ul className={styles.socials}>
                    {profile.social_links.map((link) => (
                      <li key={link.id}>
                        <a href={link.url} target="_blank" rel="noopener noreferrer me" aria-label={link.platform} className={styles.social}>
                          <SocialIcon platform={link.platform} size={18} />
                        </a>
                      </li>
                    ))}
                  </ul>
                ) : null}
              </Card>
            </aside>
          ) : null}
        </Container>
      </Section>

      {page?.sections.map((section) => (
        <SectionRenderer
          key={section.id}
          section={section}
          locale={locale}
          data={{ profile, projects: [], services: [], posts: [], testimonials, technologies: [] }}
        />
      ))}

      {testimonials.length && !page?.sections.length ? (
        <Section alt>
          <Container size="wide">
            <TestimonialList testimonials={testimonials.slice(0, 3)} />
          </Container>
        </Section>
      ) : null}

      <CtaBanner locale={locale} />
    </>
  );
}
