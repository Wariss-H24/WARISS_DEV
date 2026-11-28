import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Button, Card, Checkbox, Field, Input, Select, Textarea } from '@wariss/ui';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { Controller, FormProvider, useForm, useWatch } from 'react-hook-form';
import { useToast } from '~/components/Toaster';
import { MediaField } from '~/crud/fields/MediaField';
import { useAbilities } from '~/lib/auth';
import { errorMessage, http, validationErrors } from '~/lib/http';
import styles from './panels.module.css';

const SCHEMA_TYPES = [
  'WebPage',
  'AboutPage',
  'ContactPage',
  'CollectionPage',
  'Article',
  'BlogPosting',
  'TechArticle',
  'CreativeWork',
  'SoftwareSourceCode',
  'Service',
  'ProfilePage',
];
const LIMITS = { meta_title: 70, meta_description: 200, og_title: 100, og_description: 200 } as const;

type SeoText = Record<keyof typeof LIMITS, string>;

interface SeoValues {
  robots_index: boolean;
  robots_follow: boolean;
  canonical_url: string;
  og_image_media_id: number | null;
  schema_type: string;
  translations: { fr: SeoText; en: SeoText };
}

const emptyText = (): SeoText => ({ meta_title: '', meta_description: '', og_title: '', og_description: '' });

function toValues(seo: Record<string, unknown> | null): SeoValues {
  const translations = (seo?.translations ?? {}) as Record<string, Partial<SeoText>>;
  const text = (locale: 'fr' | 'en'): SeoText => ({
    ...emptyText(),
    ...Object.fromEntries(Object.entries(translations[locale] ?? {}).map(([key, value]) => [key, value ?? ''])),
  });

  return {
    robots_index: (seo?.robots_index as boolean | undefined) ?? true,
    robots_follow: (seo?.robots_follow as boolean | undefined) ?? true,
    canonical_url: (seo?.canonical_url as string | null) ?? '',
    og_image_media_id: (seo?.og_image_media_id as number | null) ?? null,
    schema_type: (seo?.schema_type as string | null) ?? '',
    translations: { fr: text('fr'), en: text('en') },
  };
}

/** Fiche SEO d'un contenu (D16) : titres et descriptions par langue, indexation, image de partage. */
export function SeoPanel({ type, id }: { type: string; id: number }) {
  const client = useQueryClient();
  const toast = useToast();
  const { canWrite } = useAbilities();
  const [locale, setLocale] = useState<'fr' | 'en'>('fr');
  const key = ['admin', 'seo', type, id];
  const seo = useQuery({
    queryKey: key,
    queryFn: async () => (await http.get<{ data: Record<string, unknown> | null }>(`/admin/seo/${type}/${id}`)).data.data,
  });

  if (seo.isPending) {
    return (
      <Card padded>
        <p className="mono-label">Référencement</p>
      </Card>
    );
  }

  return (
    <SeoForm
      key={JSON.stringify(seo.data)}
      initial={toValues(seo.data ?? null)}
      locale={locale}
      setLocale={setLocale}
      canWrite={canWrite}
      onSaved={() => void client.invalidateQueries({ queryKey: key })}
      type={type}
      id={id}
      toast={toast}
    />
  );
}

function SeoForm({
  initial,
  locale,
  setLocale,
  canWrite,
  onSaved,
  type,
  id,
  toast,
}: {
  initial: SeoValues;
  locale: 'fr' | 'en';
  setLocale: (locale: 'fr' | 'en') => void;
  canWrite: boolean;
  onSaved: () => void;
  type: string;
  id: number;
  toast: ReturnType<typeof useToast>;
}) {
  const methods = useForm<SeoValues>({ defaultValues: initial });
  const { register, control, handleSubmit, setError, formState } = methods;
  const texts = useWatch({ control, name: 'translations' });
  const save = useMutation({
    mutationFn: (values: SeoValues) => {
      const clean = (text: SeoText) => Object.fromEntries(Object.entries(text).map(([field, value]) => [field, value || null]));
      const en = clean(values.translations.en);

      return http.put(`/admin/seo/${type}/${id}`, {
        ...values,
        canonical_url: values.canonical_url || null,
        schema_type: values.schema_type || null,
        translations: { fr: clean(values.translations.fr), ...(Object.values(en).some(Boolean) ? { en } : {}) },
      });
    },
  });

  const submit = handleSubmit((values) =>
    save.mutate(values, {
      onSuccess: () => {
        toast.success('Référencement enregistré.');
        onSaved();
      },
      onError: (error) => {
        const fields = validationErrors(error);

        if (fields) {
          Object.entries(fields).forEach(([path, message]) => setError(path as keyof SeoValues, { message }));
        } else {
          toast.error(errorMessage(error));
        }
      },
    }),
  );

  const counter = (field: keyof SeoText) => {
    const length = (texts[locale][field] ?? '').length;

    return (
      <span className={length > LIMITS[field] ? styles.over : styles.counter}>
        {length} / {LIMITS[field]}
      </span>
    );
  };

  const fieldError = (field: keyof SeoText) => (formState.errors.translations?.[locale]?.[field]?.message as string | undefined) ?? undefined;

  return (
    <Card padded className={styles.panel}>
      <div className={styles.panelHead}>
        <Search aria-hidden="true" size={16} />
        <h2 className={styles.panelTitle}>Référencement</h2>
        <div className={styles.miniTabs} role="tablist" aria-label="Langue du référencement">
          {(['fr', 'en'] as const).map((code) => (
            <button key={code} type="button" role="tab" aria-selected={locale === code} onClick={() => setLocale(code)}>
              {code.toUpperCase()}
            </button>
          ))}
        </div>
      </div>
      <FormProvider {...methods}>
        <form onSubmit={submit} noValidate>
          <fieldset disabled={!canWrite} className={styles.stack}>
            <Field label={<>Titre {counter('meta_title')}</>} error={fieldError('meta_title')} hint="Vide : titre du contenu.">
              <Input {...register(`translations.${locale}.meta_title`)} />
            </Field>
            <Field label={<>Description {counter('meta_description')}</>} error={fieldError('meta_description')}>
              <Textarea rows={3} {...register(`translations.${locale}.meta_description`)} />
            </Field>
            <Field label={<>Titre de partage {counter('og_title')}</>} error={fieldError('og_title')}>
              <Input {...register(`translations.${locale}.og_title`)} />
            </Field>
            <Field label={<>Description de partage {counter('og_description')}</>} error={fieldError('og_description')}>
              <Textarea rows={2} {...register(`translations.${locale}.og_description`)} />
            </Field>
            <Field label="Image de partage">
              <MediaField path="og_image_media_id" kind="image" />
            </Field>
            <Field label="URL canonique" hint="Vide : adresse de la page." error={formState.errors.canonical_url?.message}>
              <Input type="url" {...register('canonical_url')} placeholder="https://" />
            </Field>
            <Field label="Type schema.org">
              <Select {...register('schema_type')}>
                <option value="">— Aucun —</option>
                {SCHEMA_TYPES.map((schema) => (
                  <option key={schema} value={schema}>
                    {schema}
                  </option>
                ))}
              </Select>
            </Field>
            <Controller
              control={control}
              name="robots_index"
              render={({ field }) => <Checkbox label="Indexer dans les moteurs" checked={field.value} onCheckedChange={field.onChange} />}
            />
            <Controller
              control={control}
              name="robots_follow"
              render={({ field }) => <Checkbox label="Suivre les liens" checked={field.value} onCheckedChange={field.onChange} />}
            />
            {canWrite ? (
              <Button type="submit" variant="secondary" size="sm" loading={save.isPending} disabled={!formState.isDirty}>
                Enregistrer le référencement
              </Button>
            ) : null}
          </fieldset>
        </form>
      </FormProvider>
    </Card>
  );
}
