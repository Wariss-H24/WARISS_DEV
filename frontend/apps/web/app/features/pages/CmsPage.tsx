import type { Page, Profile } from '@wariss/api-client';
import type { Locale } from '@wariss/i18n';
import { Container, Section } from '@wariss/ui';
import { Markdown } from '~/components/Markdown/Markdown';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { PreviewBanner } from '../content/DetailLayout';
import { SectionRenderer, type SectionData } from '../sections/SectionRenderer';

/**
 * Page éditée dans l'administration (D18) : titre, résumé, puis ses sections dans l'ordre.
 * Utilisée pour les pages système sans gabarit dédié (now, uses, mentions légales…) et les pages
 * personnalisées.
 */
export function CmsPage({ page, locale, profile, eyebrow }: { page: Page; locale: Locale; profile: Profile | null; eyebrow?: string }) {
  const data: SectionData = { profile, projects: [], services: [], posts: [], testimonials: [], technologies: [] };
  const [first, ...rest] = page.sections;
  const startsWithHero = first?.type === 'hero';

  return (
    <>
      {page.is_preview ? <PreviewBanner /> : null}
      {startsWithHero ? null : <PageHeader eyebrow={eyebrow} title={page.title} description={page.summary} />}
      {page.sections.length === 0 ? (
        <Section>
          <Container size="reading">
            <Markdown>{page.summary}</Markdown>
          </Container>
        </Section>
      ) : (
        (startsWithHero ? [first, ...rest] : page.sections).map((section) =>
          section ? <SectionRenderer key={section.id} section={section} locale={locale} data={data} /> : null,
        )
      )}
    </>
  );
}
