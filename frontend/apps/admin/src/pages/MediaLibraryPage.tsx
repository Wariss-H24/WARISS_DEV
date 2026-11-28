import { useMutation, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { Badge, Button, Card, Dialog, EmptyState, Field, Input, Select, Skeleton } from '@wariss/ui';
import { Copy, ExternalLink, Link2, Trash2, Upload } from 'lucide-react';
import { useRef, useState, type DragEvent } from 'react';
import { useSearchParams } from 'react-router';
import { ConfirmDialog } from '~/components/ConfirmDialog';
import { PageTitle } from '~/components/PageTitle';
import { useToast } from '~/components/Toaster';
import { useResourceList, useResourceOptions, type Item } from '~/crud/api';
import { MEDIA_KIND, MEDIA_VISIBILITY } from '~/crud/options';
import { MediaThumb } from '~/media/MediaPicker';
import { useUploadMedia, type MediaUsage } from '~/media/api';
import { useAbilities } from '~/lib/auth';
import { formatBytes, formatDate } from '~/lib/format';
import { errorMessage, http, validationErrors } from '~/lib/http';
import styles from '~/media/media.module.css';

const ACCEPT = '.jpg,.jpeg,.png,.webp,.pdf,.mp4,.webm';

function MediaDetail({ media, onClose }: { media: Item; onClose: () => void }) {
  const toast = useToast();
  const client = useQueryClient();
  const { canWrite } = useAbilities();
  const collections = useResourceOptions('media-collections');
  const translations = (media.translations ?? {}) as Record<string, Record<string, string | null>>;
  const [values, setValues] = useState({
    credit: (media.credit as string | null) ?? '',
    visibility: String(media.visibility),
    media_collection_id: media.media_collection_id ? String(media.media_collection_id) : '',
    fr_alt: translations.fr?.alt_text ?? '',
    fr_caption: translations.fr?.caption ?? '',
    en_alt: translations.en?.alt_text ?? '',
    en_caption: translations.en?.caption ?? '',
  });
  const [usages, setUsages] = useState<{ list: MediaUsage[]; blocking: boolean; message: string } | null>(null);
  const set = (key: keyof typeof values) => (event: { target: { value: string } }) => setValues({ ...values, [key]: event.target.value });
  const refresh = () => void client.invalidateQueries({ queryKey: ['admin', 'media'] });

  const save = useMutation({
    mutationFn: () =>
      http.patch(`/admin/media/${media.id}`, {
        credit: values.credit || null,
        visibility: values.visibility,
        media_collection_id: values.media_collection_id ? Number(values.media_collection_id) : null,
        translations: {
          fr: { alt_text: values.fr_alt || null, caption: values.fr_caption || null },
          ...(values.en_alt || values.en_caption ? { en: { alt_text: values.en_alt || null, caption: values.en_caption || null } } : {}),
        },
      }),
    onSuccess: () => {
      toast.success('Média enregistré.');
      refresh();
    },
    onError: (error) => {
      const fields = validationErrors(error);
      toast.error(fields ? Object.values(fields).join(' ') : errorMessage(error));
    },
  });

  const remove = useMutation({
    mutationFn: (confirm: boolean) => http.delete(`/admin/media/${media.id}${confirm ? '?confirm=1' : ''}`),
    onSuccess: () => {
      toast.success('Média supprimé.');
      setUsages(null);
      onClose();
      refresh();
    },
    onError: (error) => {
      if (error instanceof AxiosError && error.response?.status === 409) {
        const body = error.response.data as { message: string; usages?: MediaUsage[]; requires_confirmation?: boolean };
        setUsages({ list: body.usages ?? [], blocking: !body.requires_confirmation, message: body.message });

        return;
      }

      toast.error(errorMessage(error));
    },
  });

  const url = media.url as string | null;

  return (
    <Card padded className={styles.detail}>
      <div className={styles.detailPreview}>
        {media.kind === 'image' && url ? <img src={url} alt="" /> : <MediaThumb media={media} />}
        <dl style={{ display: 'grid', gap: 'var(--space-2)', marginTop: 'var(--space-4)', fontSize: 'var(--text-sm)' }}>
          <div>
            <dt className="mono-label">Fichier</dt>
            <dd>{String(media.original_name ?? media.external_url ?? '—')}</dd>
          </div>
          <div>
            <dt className="mono-label">Détails</dt>
            <dd>
              {String(media.mime_type ?? media.kind)} · {formatBytes(media.size_bytes as number | null)}
              {media.width ? ` · ${String(media.width)}×${String(media.height)}` : ''} · {formatDate(media.created_at as string)}
            </dd>
          </div>
        </dl>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 'var(--space-2)', marginTop: 'var(--space-4)' }}>
          {url ? (
            <>
              <Button asChild variant="secondary" size="sm">
                <a href={url} target="_blank" rel="noopener noreferrer">
                  <ExternalLink aria-hidden="true" />
                  Ouvrir
                </a>
              </Button>
              <Button variant="secondary" size="sm" onClick={() => void navigator.clipboard.writeText(url).then(() => toast.info('Adresse copiée.'))}>
                <Copy aria-hidden="true" />
                Copier l’adresse
              </Button>
            </>
          ) : null}
        </div>
      </div>

      <fieldset disabled={!canWrite} style={{ display: 'grid', gap: 'var(--space-4)', padding: 0, border: 0, minWidth: 0 }}>
        <Field label="Texte alternatif (FR)" hint="Décrit l’image pour les lecteurs d’écran.">
          <Input value={values.fr_alt} onChange={set('fr_alt')} />
        </Field>
        <Field label="Légende (FR)">
          <Input value={values.fr_caption} onChange={set('fr_caption')} />
        </Field>
        <Field label="Texte alternatif (EN)">
          <Input value={values.en_alt} onChange={set('en_alt')} />
        </Field>
        <Field label="Légende (EN)">
          <Input value={values.en_caption} onChange={set('en_caption')} />
        </Field>
        <Field label="Crédit">
          <Input value={values.credit} onChange={set('credit')} />
        </Field>
        <Field label="Visibilité" hint="Un média privé n’est jamais affiché sur le site.">
          <Select value={values.visibility} onChange={set('visibility')}>
            {MEDIA_VISIBILITY.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
        </Field>
        <Field label="Collection">
          <Select value={values.media_collection_id} onChange={set('media_collection_id')}>
            <option value="">— Aucune —</option>
            {collections.data?.map((collection) => (
              <option key={collection.id} value={collection.id}>
                {String(collection.name)}
              </option>
            ))}
          </Select>
        </Field>
        {canWrite ? (
          <div style={{ display: 'flex', gap: 'var(--space-2)', justifyContent: 'space-between' }}>
            <Button variant="danger" size="sm" onClick={() => remove.mutate(false)} loading={remove.isPending}>
              <Trash2 aria-hidden="true" />
              Supprimer
            </Button>
            <Button size="sm" onClick={() => save.mutate()} loading={save.isPending}>
              Enregistrer
            </Button>
          </div>
        ) : null}
      </fieldset>

      <ConfirmDialog
        open={usages !== null}
        onOpenChange={(open) => !open && setUsages(null)}
        title={usages?.blocking ? 'Suppression impossible' : 'Ce média est encore utilisé'}
        description={usages?.message}
        confirmLabel={usages?.blocking ? 'Compris' : 'Supprimer quand même'}
        tone={usages?.blocking ? 'primary' : 'danger'}
        loading={remove.isPending}
        onConfirm={() => (usages?.blocking ? setUsages(null) : remove.mutate(true))}
      >
        <ul style={{ display: 'grid', gap: 'var(--space-1)', marginTop: 'var(--space-4)', fontSize: 'var(--text-sm)' }}>
          {usages?.list.map((usage) => (
            <li key={`${usage.table}.${usage.column}`}>
              <Badge tone={usage.blocking ? 'danger' : 'neutral'} mono>
                {usage.table}
              </Badge>{' '}
              {usage.count} usage(s) · {usage.column}
            </li>
          ))}
        </ul>
      </ConfirmDialog>
    </Card>
  );
}

function ExternalMediaDialog({ open, onOpenChange }: { open: boolean; onOpenChange: (open: boolean) => void }) {
  const toast = useToast();
  const client = useQueryClient();
  const [url, setUrl] = useState('');
  const [kind, setKind] = useState('video');
  const [error, setError] = useState<string | undefined>();
  const create = useMutation({
    mutationFn: () => http.post('/admin/media', { external_url: url, kind, visibility: 'public' }),
    onSuccess: () => {
      toast.success('Média externe ajouté.');
      setUrl('');
      onOpenChange(false);
      void client.invalidateQueries({ queryKey: ['admin', 'media'] });
    },
    onError: (failure) => setError(validationErrors(failure)?.external_url ?? errorMessage(failure)),
  });

  return (
    <Dialog open={open} onOpenChange={onOpenChange} title="Ajouter un média externe" description="Vidéo YouTube, Vimeo… (adresse https)." closeLabel="Fermer">
      <div style={{ display: 'grid', gap: 'var(--space-4)', marginTop: 'var(--space-4)' }}>
        <Field label="Adresse" error={error} required>
          <Input type="url" value={url} onChange={(event) => setUrl(event.target.value)} placeholder="https://www.youtube.com/watch?v=…" />
        </Field>
        <Field label="Type">
          <Select value={kind} onChange={(event) => setKind(event.target.value)}>
            {MEDIA_KIND.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
        </Field>
        <Button onClick={() => create.mutate()} loading={create.isPending}>
          Ajouter
        </Button>
      </div>
    </Dialog>
  );
}

/** Bibliothèque de médias (D14). */
export function MediaLibraryPage() {
  const [params, setParams] = useSearchParams();
  const { canWrite } = useAbilities();
  const toast = useToast();
  const upload = useUploadMedia();
  const input = useRef<HTMLInputElement>(null);
  const [dragging, setDragging] = useState(false);
  const [selected, setSelected] = useState<Item | null>(null);
  const [external, setExternal] = useState(false);
  const page = Number(params.get('page') ?? '1') || 1;
  const filters = { kind: params.get('kind') ?? '', visibility: params.get('visibility') ?? '' };
  const list = useResourceList('media', { page, perPage: 30, sort: '-id', filters });

  const uploadFiles = async (files: FileList | File[]) => {
    for (const file of Array.from(files)) {
      try {
        await upload.mutateAsync({ file, visibility: 'public' });
        toast.success(`${file.name} envoyé.`);
      } catch (error) {
        toast.error(`${file.name} : ${error instanceof Error ? error.message : 'échec de l’envoi.'}`);
      }
    }
  };

  const onDrop = (event: DragEvent<HTMLDivElement>) => {
    event.preventDefault();
    setDragging(false);

    if (canWrite && event.dataTransfer.files.length) {
      void uploadFiles(event.dataTransfer.files);
    }
  };

  const setFilter = (name: string, value: string) => {
    const next = new URLSearchParams(params);

    if (value) {
      next.set(name, value);
    } else {
      next.delete(name);
    }

    next.delete('page');
    setParams(next);
  };

  return (
    <>
      <PageTitle
        title="Bibliothèque de médias"
        description="Images (JPG, PNG, WebP · 10 Mo), PDF (20 Mo), vidéos MP4/WebM (100 Mo). Les données EXIF des photos sont retirées."
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: 'Médias' }]}
        actions={
          canWrite ? (
            <>
              <Button variant="secondary" onClick={() => setExternal(true)}>
                <Link2 aria-hidden="true" />
                Média externe
              </Button>
              <Button onClick={() => input.current?.click()} loading={upload.isPending}>
                <Upload aria-hidden="true" />
                Envoyer des fichiers
              </Button>
            </>
          ) : null
        }
      />
      <input
        ref={input}
        type="file"
        multiple
        accept={ACCEPT}
        className="visually-hidden"
        onChange={(event) => event.target.files && void uploadFiles(event.target.files)}
      />

      {canWrite ? (
        <div
          className={styles.dropzone}
          data-active={dragging || undefined}
          onDragOver={(event) => {
            event.preventDefault();
            setDragging(true);
          }}
          onDragLeave={() => setDragging(false)}
          onDrop={onDrop}
        >
          <Upload aria-hidden="true" size={22} />
          <p>Glissez vos fichiers ici pour les envoyer (publics par défaut).</p>
        </div>
      ) : null}

      <div style={{ display: 'flex', gap: 'var(--space-3)', marginBottom: 'var(--space-4)', flexWrap: 'wrap' }}>
        <Select value={filters.kind} onChange={(event) => setFilter('kind', event.target.value)} aria-label="Type" style={{ width: 'auto' }}>
          <option value="">Tous les types</option>
          {MEDIA_KIND.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </Select>
        <Select value={filters.visibility} onChange={(event) => setFilter('visibility', event.target.value)} aria-label="Visibilité" style={{ width: 'auto' }}>
          <option value="">Toutes les visibilités</option>
          {MEDIA_VISIBILITY.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </Select>
      </div>

      {selected ? <MediaDetail key={selected.id} media={selected} onClose={() => setSelected(null)} /> : null}

      <div style={{ marginTop: selected ? 'var(--space-6)' : 0 }}>
        {list.isPending ? (
          <div className={styles.library}>
            {Array.from({ length: 10 }, (_, index) => (
              <Skeleton key={index} height="10rem" />
            ))}
          </div>
        ) : list.data?.data.length ? (
          <ul className={styles.library}>
            {list.data.data.map((media) => (
              <li key={media.id}>
                <button type="button" className={styles.libraryItem} aria-pressed={selected?.id === media.id} onClick={() => setSelected(media)}>
                  <MediaThumb media={media} />
                  <span className={styles.pickerName}>{String(media.original_name ?? media.external_url ?? `#${media.id}`)}</span>
                  <span className={styles.libraryMeta}>
                    <Badge mono>{String(media.kind)}</Badge>
                    {media.visibility === 'private' ? <Badge tone="warning">Privé</Badge> : null}
                    {formatBytes(media.size_bytes as number | null)}
                  </span>
                </button>
              </li>
            ))}
          </ul>
        ) : (
          <EmptyState title="Aucun média." description="Envoyez des images, des PDF ou des vidéos." />
        )}
      </div>

      {list.data && list.data.meta.last_page > 1 ? (
        <div className={styles.pickerPages}>
          <Button size="sm" variant="secondary" disabled={page <= 1} onClick={() => setParams({ ...Object.fromEntries(params), page: String(page - 1) })}>
            Précédent
          </Button>
          <span>
            Page {page} sur {list.data.meta.last_page}
          </span>
          <Button
            size="sm"
            variant="secondary"
            disabled={page >= list.data.meta.last_page}
            onClick={() => setParams({ ...Object.fromEntries(params), page: String(page + 1) })}
          >
            Suivant
          </Button>
        </div>
      ) : null}

      <ExternalMediaDialog open={external} onOpenChange={setExternal} />
    </>
  );
}
