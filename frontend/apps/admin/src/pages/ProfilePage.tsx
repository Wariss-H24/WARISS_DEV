import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { Button, Card, Input, Select, Skeleton } from '@wariss/ui';
import { Plus, Save, Trash2, UserRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import { FormProvider, useFieldArray, useForm, useFormContext } from 'react-hook-form';
import { PageTitle } from '~/components/PageTitle';
import { useToast } from '~/components/Toaster';
import { FieldRenderer } from '~/crud/fields/FieldRenderer';
import { formValues, LOCALES, payloadFrom, type FormLocale } from '~/crud/form-values';
import type { ResourceConfig } from '~/crud/types';
import { useAbilities } from '~/lib/auth';
import { errorMessage, http, validationErrors } from '~/lib/http';
import styles from '~/crud/ResourceFormPage.module.css';

/** Description du profil, utilisée par le moteur de formulaires (ressource unique, D1). */
const PROFILE: ResourceConfig = {
  key: 'profile',
  label: 'Profil',
  singular: 'profil',
  icon: UserRound,
  group: 'Identité',
  columns: [],
  groups: [
    {
      title: 'Identité',
      fields: [
        { name: 'first_name', label: 'Prénom', type: 'text', required: true },
        { name: 'last_name', label: 'Nom', type: 'text', required: true },
        { name: 'display_name', label: 'Nom affiché', type: 'text', required: true },
        { name: 'public_email', label: 'E-mail public', type: 'text', required: true, hint: 'Affiché sur le site : jamais l’adresse de connexion.' },
        { name: 'public_phone', label: 'Téléphone public', type: 'text' },
        { name: 'country_code', label: 'Pays (code ISO : FR, BJ…)', type: 'text' },
        { name: 'avatar_media_id', label: 'Photo de profil', type: 'media' },
        { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
      ],
    },
    {
      title: 'Présentation',
      fields: [
        { name: 'headline', label: 'Titre professionnel', type: 'text', translatable: true, required: true, wide: true },
        { name: 'short_bio', label: 'Présentation courte', type: 'textarea', translatable: true, required: true, wide: true, hint: '500 caractères au plus.' },
        { name: 'biography', label: 'Biographie', type: 'markdown', translatable: true, required: true, wide: true },
        { name: 'location_label', label: 'Localisation', type: 'text', translatable: true },
      ],
    },
    {
      title: 'Disponibilité',
      fields: [
        {
          name: 'availability_status',
          label: 'Disponibilité',
          type: 'select',
          options: [
            { value: 'available', label: 'Disponible' },
            { value: 'limited', label: 'Disponibilité limitée' },
            { value: 'unavailable', label: 'Indisponible' },
          ],
          defaultValue: 'available',
        },
        { name: 'available_from', label: 'Disponible à partir du', type: 'date' },
        { name: 'availability_note', label: 'Note de disponibilité', type: 'text', translatable: true, wide: true },
      ],
    },
  ],
};

const LEVELS = [
  { value: 'native', label: 'Langue maternelle' },
  { value: 'fluent', label: 'Courant' },
  { value: 'professional', label: 'Professionnel' },
  { value: 'intermediate', label: 'Intermédiaire' },
  { value: 'basic', label: 'Notions' },
];

function Languages() {
  const { fields, append, remove } = useFieldArray({ name: 'spoken_languages' });

  return (
    <Card padded className={styles.group}>
      <div className={styles.groupHead}>
        <h2 className={styles.groupTitle}>Langues parlées</h2>
        <p className={styles.groupDescription}>Code ISO à deux lettres (fr, en…) et niveau.</p>
      </div>
      {fields.map((item, index) => (
        <div key={item.id} style={{ display: 'flex', gap: 'var(--space-2)', alignItems: 'center' }}>
          <LanguageRow index={index} />
          <Button type="button" variant="ghost" size="sm" iconOnly aria-label="Retirer la langue" onClick={() => remove(index)}>
            <Trash2 aria-hidden="true" />
          </Button>
        </div>
      ))}
      <Button type="button" variant="secondary" size="sm" onClick={() => append({ code: '', level: 'professional' })}>
        <Plus aria-hidden="true" />
        Ajouter une langue
      </Button>
    </Card>
  );
}

function LanguageRow({ index }: { index: number }) {
  const { register } = useFormContext();

  return (
    <>
      <Input {...register(`spoken_languages.${index}.code`)} placeholder="fr" maxLength={2} aria-label="Code de langue" style={{ width: '5rem' }} />
      <Select {...register(`spoken_languages.${index}.level`)} aria-label="Niveau">
        {LEVELS.map((level) => (
          <option key={level.value} value={level.value}>
            {level.label}
          </option>
        ))}
      </Select>
    </>
  );
}

function ProfileForm({ profile, exists }: { profile: Record<string, unknown> | null; exists: boolean }) {
  const toast = useToast();
  const client = useQueryClient();
  const { canWrite } = useAbilities();
  const [locale, setLocale] = useState<FormLocale>('fr');
  const initial = {
    ...formValues(PROFILE, profile ? ({ id: 0, ...profile } as never) : undefined),
    spoken_languages: (profile?.spoken_languages as unknown[]) ?? [],
  };
  const methods = useForm({ defaultValues: initial });

  const save = useMutation({
    mutationFn: (values: Record<string, unknown>) =>
      http.put('/admin/profile', { ...payloadFrom(PROFILE, values, !exists), spoken_languages: values.spoken_languages }),
    onSuccess: () => {
      toast.success('Profil enregistré.');
      void client.invalidateQueries({ queryKey: ['admin', 'profile'] });
    },
    onError: (error) => {
      const fields = validationErrors(error);

      if (fields) {
        Object.entries(fields).forEach(([path, message]) => methods.setError(path as never, { message }));
        toast.error('Certains champs sont à corriger.');
      } else {
        toast.error(errorMessage(error));
      }
    },
  });

  return (
    <FormProvider {...methods}>
      <form onSubmit={methods.handleSubmit((values) => save.mutate(values))} noValidate className={styles.main}>
        <div className={styles.locales} role="tablist" aria-label="Langue des contenus">
          {LOCALES.map((code) => (
            <button key={code} type="button" role="tab" aria-selected={locale === code} className={styles.localeTab} onClick={() => setLocale(code)}>
              {code === 'fr' ? 'Français' : 'English'}
            </button>
          ))}
        </div>
        <fieldset disabled={!canWrite} className={styles.fieldset}>
          {PROFILE.groups.map((group) => (
            <Card key={group.title} padded className={styles.group}>
              <h2 className={styles.groupTitle}>{group.title}</h2>
              <div className={styles.grid}>
                {group.fields.map((field) => (
                  <FieldRenderer key={field.name} field={field} locale={locale} />
                ))}
              </div>
            </Card>
          ))}
          <Languages />
        </fieldset>
        {canWrite ? (
          <div className={styles.bar}>
            <span className={styles.dirty}>{methods.formState.isDirty ? 'Modifications non enregistrées' : ''}</span>
            <Button type="submit" loading={save.isPending}>
              <Save aria-hidden="true" />
              Enregistrer le profil
            </Button>
          </div>
        ) : null}
      </form>
    </FormProvider>
  );
}

/** Profil public du propriétaire (D1) : ressource unique, créée au premier enregistrement. */
export function ProfilePage() {
  const profile = useQuery({
    queryKey: ['admin', 'profile'],
    queryFn: async () => {
      try {
        return (await http.get<{ data: Record<string, unknown> }>('/admin/profile')).data.data;
      } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 404) {
          return null;
        }

        throw error;
      }
    },
  });

  useEffect(() => {
    document.title = 'Profil · Administration WARISS.DEV';
  }, []);

  return (
    <>
      <PageTitle
        title="Profil"
        description="Informations publiques affichées sur le site (jamais les données de connexion)."
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: 'Profil' }]}
      />
      {profile.isPending ? (
        <Skeleton height="24rem" />
      ) : profile.isError ? (
        <p role="alert">{errorMessage(profile.error)}</p>
      ) : (
        <ProfileForm key={JSON.stringify(profile.data?.updated_at ?? 'new')} profile={profile.data} exists={profile.data !== null} />
      )}
    </>
  );
}
