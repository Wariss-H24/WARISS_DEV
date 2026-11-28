/**
 * Conversion entre un contenu renvoyé par l'API (AdminResource) et les valeurs du formulaire,
 * puis entre les valeurs du formulaire et le corps envoyé à l'API (FormRequest).
 */
import type { Item } from './api';
import type { FieldDef, ResourceConfig } from './types';

export const LOCALES = ['fr', 'en'] as const;
export type FormLocale = (typeof LOCALES)[number];

type Values = Record<string, unknown>;

export function allFields(config: ResourceConfig): FieldDef[] {
  return config.groups.flatMap((group) => group.fields);
}

/** « 2025-01-15T00:00:00.000000Z » → « 2025-01-15 » */
function toDateInput(value: unknown): string {
  return typeof value === 'string' && value ? value.slice(0, 10) : '';
}

/** ISO → « 2025-01-15T09:30 » (heure locale, pour <input type="datetime-local">) */
function toDateTimeInput(value: unknown): string {
  if (typeof value !== 'string' || !value) {
    return '';
  }

  const date = new Date(value);
  const pad = (number: number) => String(number).padStart(2, '0');

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function inputValue(field: FieldDef, value: unknown): unknown {
  switch (field.type) {
    case 'date':
      return toDateInput(value);
    case 'datetime':
      return toDateTimeInput(value);
    case 'boolean':
      return Boolean(value);
    case 'relations':
      return Array.isArray(value) ? value.map(Number) : [];
    case 'json':
      return value && typeof value === 'object' && Object.keys(value).length ? JSON.stringify(value, null, 2) : '';
    case 'select':
      return value === null || value === undefined ? '' : String(value);
    case 'number':
    case 'relation':
    case 'media':
      return value ?? null;
    default:
      return value ?? '';
  }
}

function emptyValue(field: FieldDef): unknown {
  if (field.defaultValue !== undefined) {
    return inputValue(field, field.defaultValue);
  }

  return inputValue(field, field.type === 'boolean' ? false : field.type === 'relations' ? [] : field.type === 'children' ? [] : null);
}

function translationsOf(source: Values | undefined, fields: FieldDef[], fromItem: boolean): Record<FormLocale, Values> {
  const translations = (source?.translations ?? {}) as Record<string, Values>;
  const result = {} as Record<FormLocale, Values>;

  for (const locale of LOCALES) {
    result[locale] = {};

    for (const field of fields.filter((candidate) => candidate.translatable)) {
      result[locale][field.name] = fromItem ? inputValue(field, translations[locale]?.[field.name]) : emptyValue(field);
    }
  }

  return result;
}

/** Valeurs d'un élément enfant (fonctionnalité, section…) pour le formulaire. */
export function childValues(fields: FieldDef[], child?: Values): Values {
  const values: Values = {};

  for (const field of fields.filter((candidate) => !candidate.translatable)) {
    values[field.name] = child ? inputValue(field, child[field.name]) : emptyValue(field);
  }

  values.translations = translationsOf(child, fields, Boolean(child));

  return values;
}

/** Valeurs initiales du formulaire (création si `item` est absent). */
export function formValues(config: ResourceConfig, item?: Item): Values {
  const fields = allFields(config);
  const values: Values = {};

  for (const field of fields.filter((candidate) => !candidate.translatable)) {
    if (field.type === 'children') {
      const children = (item?.[field.name] as Values[] | undefined) ?? [];
      values[field.name] = children.map((child) => childValues(field.fields ?? [], child));
    } else {
      values[field.name] = item ? inputValue(field, item[field.name]) : emptyValue(field);
    }
  }

  values.translations = translationsOf(item, fields, Boolean(item));

  return values;
}

function outputValue(field: FieldDef, value: unknown): unknown {
  switch (field.type) {
    case 'number':
      return value === '' || value === null || value === undefined || Number.isNaN(value) ? null : Number(value);
    case 'relation':
    case 'media':
      return value === '' || value === null || value === undefined ? null : Number(value);
    case 'boolean':
      return Boolean(value);
    case 'datetime':
      return typeof value === 'string' && value ? new Date(value).toISOString() : null;
    case 'date':
    case 'select':
    case 'text':
    case 'textarea':
    case 'markdown':
    case 'url':
    case 'slug':
    case 'color':
      return value === '' || value === undefined ? null : value;
    case 'json':
      if (typeof value !== 'string' || value.trim() === '') {
        return {};
      }

      return JSON.parse(value) as unknown;
    default:
      return value;
  }
}

/** Traductions envoyées : une langue entièrement vide n'est pas envoyée (seul le français est obligatoire). */
function outputTranslations(fields: FieldDef[], translations: Record<string, Values> | undefined): Record<string, Values> | undefined {
  const translated = fields.filter((field) => field.translatable);

  if (!translated.length) {
    return undefined;
  }

  const result: Record<string, Values> = {};

  for (const locale of LOCALES) {
    const values: Values = {};
    let filled = false;

    for (const field of translated) {
      const value = outputValue(field, translations?.[locale]?.[field.name]);
      values[field.name] = value;
      filled ||= value !== null && value !== '';
    }

    if (filled || locale === 'fr') {
      result[locale] = values;
    }
  }

  return result;
}

/**
 * Corps de la requête : champs visibles du formulaire, au format attendu par l'API.
 * Une valeur JSON invalide lève une erreur (champ signalé dans le formulaire).
 */
export function payloadFrom(config: ResourceConfig, values: Values, isNew: boolean): Values {
  const fields = allFields(config).filter((field) => !field.only || field.only === (isNew ? 'create' : 'update'));
  const payload: Values = {};

  for (const field of fields.filter((candidate) => !candidate.translatable)) {
    if (field.visibleIf && !field.visibleIf(values)) {
      continue;
    }

    if (field.type === 'children') {
      const childFields = field.fields ?? [];
      payload[field.name] = ((values[field.name] as Values[] | undefined) ?? []).map((child) => {
        const output: Values = {};

        for (const childField of childFields.filter((candidate) => !candidate.translatable)) {
          output[childField.name] = outputValue(childField, child[childField.name]);
        }

        output.translations = outputTranslations(childFields, child.translations as Record<string, Values>);

        return output;
      });
      continue;
    }

    payload[field.name] = outputValue(field, values[field.name]);
  }

  const translations = outputTranslations(fields, values.translations as Record<string, Values>);

  if (translations) {
    payload.translations = translations;
  }

  if (config.key === 'redirects' && payload.status_code) {
    payload.status_code = Number(payload.status_code);
  }

  return payload;
}

/** Transforme un texte en slug d'adresse (minuscules, sans accents, tirets). */
export function slugify(text: string): string {
  return text
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 120);
}
