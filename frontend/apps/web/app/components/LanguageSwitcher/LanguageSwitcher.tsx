import type { Locale } from '@wariss/i18n';
import { Button, Tooltip } from '@wariss/ui';
import { Languages } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link, useLocation, useMatches } from 'react-router';
import { otherLocale } from '~/lib/preferences';
import { switchLocalePath } from '~/lib/routes';

/**
 * Lien vers la même page dans l'autre langue. Une page de contenu déclare ses chemins traduits
 * dans les données de son loader (`alternates`), car le slug change d'une langue à l'autre.
 */
export function useAlternatePath(target: Locale): string {
  const location = useLocation();
  const matches = useMatches();
  let alternates: Partial<Record<Locale, string>> | undefined;

  for (let index = matches.length - 1; index >= 0 && !alternates; index--) {
    alternates = (matches[index]?.loaderData as { alternates?: Partial<Record<Locale, string>> } | undefined)?.alternates;
  }

  return switchLocalePath(location.pathname, target, alternates) + location.search;
}

export function LanguageSwitcher({ locale, className }: { locale: Locale; className?: string }) {
  const { t } = useTranslation();
  const target = otherLocale(locale);
  const href = useAlternatePath(target);

  return (
    <Tooltip content={t('language.switchTo')}>
      <Button asChild variant="ghost" size="sm" className={className}>
        <Link to={href} hrefLang={target} lang={target} aria-label={t('language.switchTo')} reloadDocument={false}>
          <Languages aria-hidden="true" />
          <span style={{ fontFamily: 'var(--font-mono)', textTransform: 'uppercase' }}>{target}</span>
        </Link>
      </Button>
    </Tooltip>
  );
}
