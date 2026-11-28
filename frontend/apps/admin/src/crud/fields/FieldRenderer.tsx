import { Button, Checkbox, Field, Input, Select, Textarea } from '@wariss/ui';
import { Wand2 } from 'lucide-react';
import { Controller, useFormContext, type FieldError, type FieldErrors } from 'react-hook-form';
import { labelOf } from '~/lib/format';
import { useResourceOptions } from '../api';
import { slugify, type FormLocale } from '../form-values';
import type { FieldDef } from '../types';
import { ChildrenField } from './ChildrenField';
import { MarkdownField } from './MarkdownField';
import { MediaField } from './MediaField';
import { MultiRelationField } from './MultiRelationField';
import styles from './fields.module.css';

/** Chemin du champ dans les valeurs du formulaire (et dans les erreurs de l'API). */
export function fieldPath(field: FieldDef, locale: FormLocale, prefix = ''): string {
  return field.translatable ? `${prefix}translations.${locale}.${field.name}` : `${prefix}${field.name}`;
}

export function errorAt(errors: FieldErrors, path: string): string | undefined {
  const error = path
    .split('.')
    .reduce<unknown>((node, key) => (node && typeof node === 'object' ? (node as Record<string, unknown>)[key] : undefined), errors) as FieldError | undefined;

  return typeof error?.message === 'string' ? error.message : undefined;
}

function RelationSelect({ field, path }: { field: FieldDef; path: string }) {
  const { register } = useFormContext();
  const options = useResourceOptions(field.resource);

  return (
    <Select {...register(path, { setValueAs: (value: string) => (value === '' ? null : Number(value)) })} disabled={options.isPending}>
      <option value="">{options.isPending ? 'Chargement…' : '— Aucun —'}</option>
      {options.data?.map((item) => (
        <option key={item.id} value={item.id}>
          {labelOf(item)}
        </option>
      ))}
    </Select>
  );
}

/** Rendu d'un champ de formulaire selon son type. */
export function FieldRenderer({ field, locale, prefix = '' }: { field: FieldDef; locale: FormLocale; prefix?: string }) {
  const { register, control, formState, getValues, setValue, watch } = useFormContext();
  const path = fieldPath(field, locale, prefix);
  const error = errorAt(formState.errors, path);
  const label = field.translatable ? `${field.label} (${locale.toUpperCase()})` : field.label;
  const className = field.wide || field.type === 'markdown' || field.type === 'children' || field.type === 'relations' ? styles.wide : undefined;
  const values = field.visibleIf ? (watch() as Record<string, unknown>) : null;

  if (field.visibleIf && values && !field.visibleIf(values)) {
    return null;
  }

  if (field.type === 'children') {
    return (
      <div className={styles.wide}>
        <ChildrenField field={field} locale={locale} />
      </div>
    );
  }

  if (field.type === 'boolean') {
    return (
      <div className={className}>
        <Controller
          control={control}
          name={path}
          render={({ field: control }) => (
            <Checkbox label={label} checked={Boolean(control.value)} onCheckedChange={control.onChange} invalid={Boolean(error)} />
          )}
        />
        {error ? <p className={styles.error}>{error}</p> : null}
      </div>
    );
  }

  const input = (() => {
    switch (field.type) {
      case 'textarea':
        return <Textarea rows={3} {...register(path)} placeholder={field.placeholder} />;
      case 'markdown':
        return <MarkdownField path={path} />;
      case 'number':
        return (
          <Input type="number" step="any" inputMode="decimal" {...register(path, { setValueAs: (value: string) => (value === '' ? null : Number(value)) })} />
        );
      case 'date':
        return <Input type="date" {...register(path)} />;
      case 'datetime':
        return <Input type="datetime-local" {...register(path)} />;
      case 'url':
        return <Input type="url" inputMode="url" placeholder={field.placeholder ?? 'https://'} {...register(path)} />;
      case 'color':
        return (
          <div className={styles.colorRow}>
            <input
              type="color"
              className={styles.color}
              value={(watch(path) as string) || '#5b8cff'}
              onChange={(event) => setValue(path, event.target.value, { shouldDirty: true })}
              aria-label={`${label} : sélecteur`}
            />
            <Input {...register(path)} placeholder="#5B8CFF" />
          </div>
        );
      case 'select':
        return (
          <Select {...register(path)}>
            <option value="">— Choisir —</option>
            {field.options?.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
        );
      case 'relation':
        return <RelationSelect field={field} path={path} />;
      case 'relations':
        return <MultiRelationField field={field} path={path} />;
      case 'media':
        return <MediaField path={path} kind={field.mediaKind ?? 'image'} />;
      case 'json':
        return <Textarea rows={4} spellCheck={false} className={styles.mono} {...register(path)} placeholder="{}" />;
      case 'slug': {
        const sourcePath = field.slugFrom ? fieldPath({ ...field, name: field.slugFrom }, locale, prefix) : null;

        return (
          <div className={styles.slugRow}>
            <Input {...register(path)} placeholder="mon-adresse" className={styles.mono} />
            {sourcePath ? (
              <Button
                type="button"
                variant="secondary"
                size="sm"
                onClick={() => setValue(path, slugify(String(getValues(sourcePath) ?? '')), { shouldDirty: true })}
                aria-label="Générer depuis le titre"
              >
                <Wand2 aria-hidden="true" />
                Générer
              </Button>
            ) : null}
          </div>
        );
      }
      default:
        return <Input {...register(path)} placeholder={field.placeholder} />;
    }
  })();

  return (
    <Field label={label} hint={field.hint} error={error} required={field.required} className={className}>
      {input}
    </Field>
  );
}
