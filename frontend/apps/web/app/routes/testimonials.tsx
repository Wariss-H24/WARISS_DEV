import type { Testimonial } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Container, EmptyState, Section } from '@wariss/ui';
import { Quote } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/testimonials';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { CtaBanner } from '~/features/home/CtaBanner';
import { TestimonialList } from '~/features/home/Testimonials';
import { apiGetOr } from '~/lib/api.server';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const testimonials = await apiGetOr<{ data: Testimonial[] }>('/testimonials', locale, { data: [] });

  return { locale, testimonials: testimonials.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.testimonialsTitle'),
    description: t('pages.testimonialsDescription'),
    paths: staticPaths('testimonials'),
  });
}

export default function Testimonials() {
  const { locale, testimonials } = useLoaderData<typeof loader>();
  const { t } = useTranslation();

  return (
    <>
      <PageHeader eyebrow={t('nav.testimonials')} title={t('pages.testimonialsTitle')} description={t('pages.testimonialsDescription')} />
      <Section>
        <Container size="wide">
          {testimonials.length ? (
            <TestimonialList testimonials={testimonials} />
          ) : (
            <EmptyState icon={<Quote aria-hidden="true" size={22} />} title={t('pages.empty')} />
          )}
        </Container>
      </Section>
      <CtaBanner locale={locale} />
    </>
  );
}
