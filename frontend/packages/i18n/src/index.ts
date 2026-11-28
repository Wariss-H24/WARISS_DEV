/**
 * Textes d'interface FR / EN (les contenus, eux, viennent de l'API déjà traduits).
 *
 * Une instance i18next par requête côté serveur (createI18n) : deux visiteurs servis en même
 * temps dans deux langues ne partagent jamais d'état.
 */
import i18next, { type i18n } from 'i18next';
import enCommon from './locales/en/common.json';
import frCommon from './locales/fr/common.json';

export const LOCALES = ['fr', 'en'] as const;
export type Locale = (typeof LOCALES)[number];
export const DEFAULT_LOCALE: Locale = 'fr';

export const resources = {
  fr: { common: frCommon },
  en: { common: enCommon },
} as const;

export type Namespace = keyof (typeof resources)['fr'];

export function isLocale(value: unknown): value is Locale {
  return typeof value === 'string' && (LOCALES as readonly string[]).includes(value);
}

/**
 * Crée une instance i18next initialisée de façon synchrone (toutes les ressources sont incluses).
 */
export function createI18n(locale: Locale): i18n {
  const instance = i18next.createInstance();

  void instance.init({
    lng: locale,
    fallbackLng: DEFAULT_LOCALE,
    supportedLngs: [...LOCALES],
    resources,
    ns: ['common'],
    defaultNS: 'common',
    initAsync: false,
    interpolation: { escapeValue: false },
    returnNull: false,
  });

  return instance;
}

declare module 'i18next' {
  interface CustomTypeOptions {
    defaultNS: 'common';
    resources: (typeof resources)['fr'];
  }
}
