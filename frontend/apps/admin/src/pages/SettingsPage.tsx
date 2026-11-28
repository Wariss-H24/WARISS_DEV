import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Badge, Button, Card, Checkbox, Field, Input, Skeleton, Textarea } from '@wariss/ui';
import { Save } from 'lucide-react';
import { useState } from 'react';
import { PageTitle } from '~/components/PageTitle';
import { useToast } from '~/components/Toaster';
import { useResourceItem } from '~/crud/api';
import { MediaPicker, MediaThumb } from '~/media/MediaPicker';
import { useAbilities } from '~/lib/auth';
import { errorMessage, http, validationErrors } from '~/lib/http';
import styles from '~/crud/ResourceFormPage.module.css';

interface Setting {
  id: number;
  group: string;
  key: string;
  type: 'string' | 'text' | 'bool' | 'int' | 'json' | 'media';
  is_translatable: boolean;
  is_public: boolean;
  value: unknown;
  translations: Record<string, string | null> | null;
}

const GROUP_LABELS: Record<string, string> = {
  general: 'Général',
  branding: 'Identité visuelle',
  contact: 'Contact',
  footer: 'Pied de page',
  seo: 'Référencement',
  maintenance: 'Maintenance',
};

const KEY_LABELS: Record<string, string> = {
  site_name: 'Nom du site',
  tagline: 'Slogan',
  description: 'Description',
  logo: 'Logo',
  favicon: 'Favicon',
  public_email: 'E-mail public',
  text: 'Texte du pied de page',
  title_suffix: 'Suffixe des titres',
  default_og_image: 'Image de partage par défaut',
  enabled: 'Mode maintenance',
};

type Draft = Record<string, { value?: unknown; translations?: Record<string, string> }>;

function MediaSetting({ value, onChange, disabled }: { value: number | null; onChange: (id: number | null) => void; disabled: boolean }) {
  const [open, setOpen] = useState(false);
  const media = useResourceItem('media', value);

  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-3)' }}>
      {media.data ? (
        <MediaThumb media={media.data} size="sm" />
      ) : (
        <span style={{ color: 'var(--color-text-subtle)', fontSize: 'var(--text-sm)' }}>Aucun média</span>
      )}
      {disabled ? null : (
        <>
          <Button type="button" variant="secondary" size="sm" onClick={() => setOpen(true)}>
            Choisir
          </Button>
          {value ? (
            <Button type="button" variant="ghost" size="sm" onClick={() => onChange(null)}>
              Retirer
            </Button>
          ) : null}
        </>
      )}
      <MediaPicker open={open} onOpenChange={setOpen} kind="image" onSelect={(item) => onChange(item.id)} />
    </div>
  );
}

/** Réglages du site (D2) : mise à jour par lot ; le compte viewer ne voit que les réglages publics. */
export function SettingsPage() {
  const toast = useToast();
  const client = useQueryClient();
  const { canWrite } = useAbilities();
  const settings = useQuery({ queryKey: ['admin', 'settings'], queryFn: async () => (await http.get<{ data: Setting[] }>('/admin/settings')).data.data });
  const [draft, setDraft] = useState<Draft>({});
  const [errors, setErrors] = useState<Record<string, string>>({});
  const id = (setting: Setting) => `${setting.group}.${setting.key}`;

  const save = useMutation({
    mutationFn: () =>
      http.patch('/admin/settings', {
        settings: Object.entries(draft).map(([identifier, change]) => {
          const [group, key] = identifier.split('.');

          return { group, key, ...change };
        }),
      }),
    onSuccess: () => {
      toast.success('Réglages enregistrés.');
      setDraft({});
      setErrors({});
      void client.invalidateQueries({ queryKey: ['admin', 'settings'] });
    },
    onError: (error) => {
      const fields = validationErrors(error);

      if (fields) {
        const entries = Object.keys(draft);
        const mapped: Record<string, string> = {};

        for (const [path, message] of Object.entries(fields)) {
          const index = Number(path.split('.')[1]);
          const identifier = entries[index];

          if (identifier) {
            mapped[identifier] = message;
          }
        }

        setErrors(mapped);
        toast.error('Certains réglages sont à corriger.');
      } else {
        toast.error(errorMessage(error));
      }
    },
  });

  const valueOf = (setting: Setting) => (id(setting) in draft && 'value' in (draft[id(setting)] ?? {}) ? draft[id(setting)]?.value : setting.value);
  const translationOf = (setting: Setting, locale: string) => draft[id(setting)]?.translations?.[locale] ?? setting.translations?.[locale] ?? '';
  const setValue = (setting: Setting, value: unknown) => setDraft({ ...draft, [id(setting)]: { value } });
  const setTranslation = (setting: Setting, locale: string, text: string) =>
    setDraft({
      ...draft,
      [id(setting)]: { translations: { fr: translationOf(setting, 'fr'), en: translationOf(setting, 'en'), [locale]: text } },
    });

  const groups = new Map<string, Setting[]>();

  for (const setting of settings.data ?? []) {
    groups.set(setting.group, [...(groups.get(setting.group) ?? []), setting]);
  }

  const render = (setting: Setting) => {
    const label = KEY_LABELS[setting.key] ?? setting.key;
    const error = errors[id(setting)];
    const badge = setting.is_public ? null : <Badge tone="warning">privé</Badge>;

    if (setting.is_translatable) {
      return (['fr', 'en'] as const).map((locale) => (
        <Field
          key={locale}
          label={
            <>
              {label} ({locale.toUpperCase()}) {badge}
            </>
          }
          error={locale === 'fr' ? error : undefined}
        >
          {setting.type === 'text' ? (
            <Textarea rows={3} value={translationOf(setting, locale)} onChange={(event) => setTranslation(setting, locale, event.target.value)} />
          ) : (
            <Input value={translationOf(setting, locale)} onChange={(event) => setTranslation(setting, locale, event.target.value)} />
          )}
        </Field>
      ));
    }

    switch (setting.type) {
      case 'bool':
        return <Checkbox key={id(setting)} label={label} checked={Boolean(valueOf(setting))} onCheckedChange={(checked) => setValue(setting, checked)} />;
      case 'media':
        return (
          <Field
            key={id(setting)}
            label={
              <>
                {label} {badge}
              </>
            }
            error={error}
          >
            <MediaSetting value={(valueOf(setting) as number | null) ?? null} onChange={(mediaId) => setValue(setting, mediaId)} disabled={!canWrite} />
          </Field>
        );
      case 'int':
        return (
          <Field
            key={id(setting)}
            label={
              <>
                {label} {badge}
              </>
            }
            error={error}
          >
            <Input
              type="number"
              value={String(valueOf(setting) ?? '')}
              onChange={(event) => setValue(setting, event.target.value === '' ? null : Number(event.target.value))}
            />
          </Field>
        );
      case 'json':
        return (
          <Field
            key={id(setting)}
            label={
              <>
                {label} {badge}
              </>
            }
            error={error}
            hint="JSON"
          >
            <Textarea
              rows={4}
              defaultValue={JSON.stringify(valueOf(setting) ?? {}, null, 2)}
              onBlur={(event) => {
                try {
                  setValue(setting, JSON.parse(event.target.value || '{}'));
                } catch {
                  setErrors({ ...errors, [id(setting)]: 'JSON invalide.' });
                }
              }}
            />
          </Field>
        );
      case 'text':
        return (
          <Field
            key={id(setting)}
            label={
              <>
                {label} {badge}
              </>
            }
            error={error}
          >
            <Textarea rows={3} value={String(valueOf(setting) ?? '')} onChange={(event) => setValue(setting, event.target.value || null)} />
          </Field>
        );
      default:
        return (
          <Field
            key={id(setting)}
            label={
              <>
                {label} {badge}
              </>
            }
            error={error}
          >
            <Input value={String(valueOf(setting) ?? '')} onChange={(event) => setValue(setting, event.target.value || null)} />
          </Field>
        );
    }
  };

  return (
    <>
      <PageTitle
        title="Réglages du site"
        description="Nom, slogan, logo, e-mail public, pied de page, référencement par défaut."
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: 'Réglages' }]}
      />
      {settings.isPending ? (
        <Skeleton height="24rem" />
      ) : settings.isError ? (
        <p role="alert">{errorMessage(settings.error)}</p>
      ) : (
        <div className={styles.main}>
          <fieldset disabled={!canWrite} className={styles.fieldset}>
            {[...groups.entries()].map(([group, items]) => (
              <Card key={group} padded className={styles.group}>
                <h2 className={styles.groupTitle}>{GROUP_LABELS[group] ?? group}</h2>
                <div className={styles.grid}>{items.map(render)}</div>
              </Card>
            ))}
          </fieldset>
          {canWrite ? (
            <div className={styles.bar}>
              <span className={styles.dirty}>{Object.keys(draft).length ? `${Object.keys(draft).length} réglage(s) modifié(s)` : 'Tout est enregistré'}</span>
              <Button onClick={() => save.mutate()} loading={save.isPending} disabled={!Object.keys(draft).length}>
                <Save aria-hidden="true" />
                Enregistrer
              </Button>
            </div>
          ) : null}
        </div>
      )}
    </>
  );
}
