import type { ResumeInfo } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Badge, Button, Card, Container, EmptyState, Section } from '@wariss/ui';
import { FileDown, FileText } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/resume';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { apiGetOr } from '~/lib/api.server';
import { formatDate, formatSize } from '~/lib/format';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import { BROWSER_API_URL } from '~/lib/tracking';
import styles from './resume.module.css';

export async function loader({ params }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const resumes = await apiGetOr<{ data: ResumeInfo[] }>('/resume', locale, { data: [] });

  return { locale, resumes: resumes.data };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('pages.resumeTitle'),
    description: t('pages.resumeDescription'),
    paths: staticPaths('resume'),
  });
}

function downloadUrl(resume: ResumeInfo): string {
  return `${BROWSER_API_URL}/public/resume/download?locale=${resume.locale}&target=${resume.target}`;
}

export default function Resume() {
  const { locale, resumes } = useLoaderData<typeof loader>();
  const { t } = useTranslation();
  const ordered = [...resumes].sort((a, b) => Number(b.locale === locale) - Number(a.locale === locale) || Number(b.is_default) - Number(a.is_default));

  return (
    <>
      <PageHeader eyebrow={t('nav.resume')} title={t('pages.resumeTitle')} description={t('pages.resumeDescription')} />
      <Section>
        <Container>
          {ordered.length ? (
            <ul className={styles.list}>
              {ordered.map((resume) => (
                <li key={`${resume.locale}-${resume.target}`}>
                  <Card padded spotlight className={styles.card}>
                    <span className={styles.icon} aria-hidden="true">
                      <FileText size={26} />
                    </span>
                    <div className={styles.info}>
                      <h2 className={styles.title}>{resume.title}</h2>
                      <p className={styles.meta}>
                        <Badge mono>{t(`language.${resume.locale}`)}</Badge>
                        {resume.version_number ? <span>{t('pages.resumeVersion', { version: resume.version_number })}</span> : null}
                        {resume.updated_at ? <span>{t('pages.resumeUpdated', { date: formatDate(resume.updated_at, locale) })}</span> : null}
                        {resume.size_bytes ? <span>{formatSize(resume.size_bytes, locale)} · PDF</span> : null}
                      </p>
                    </div>
                    <Button asChild variant={resume.locale === locale ? 'gradient' : 'secondary'}>
                      <a href={downloadUrl(resume)} download hrefLang={resume.locale}>
                        <FileDown aria-hidden="true" />
                        {t('actions.downloadResume')}
                      </a>
                    </Button>
                  </Card>
                </li>
              ))}
            </ul>
          ) : (
            <EmptyState icon={<FileText aria-hidden="true" size={22} />} title={t('pages.resumeUnavailable')} />
          )}
        </Container>
      </Section>
    </>
  );
}
