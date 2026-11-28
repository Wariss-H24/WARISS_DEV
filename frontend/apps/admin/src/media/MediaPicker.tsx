import { Badge, Button, Dialog, EmptyState, Skeleton } from '@wariss/ui';
import { FileText, ImageOff, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import { useResourceList, type Item } from '~/crud/api';
import { useUploadMedia } from './api';
import styles from './media.module.css';

export type MediaKindFilter = 'image' | 'document' | 'video' | 'icon' | 'any';

/** Vignette d'un média (image, ou icône du type de fichier). */
export function MediaThumb({ media, size = 'md' }: { media: Item; size?: 'sm' | 'md' }) {
  const url = media.url as string | null;
  const kind = media.kind as string;

  return (
    <span className={size === 'sm' ? styles.thumbSmall : styles.thumb}>
      {kind === 'image' || kind === 'icon' ? (
        url ? (
          <img src={url} alt={((media.translations as Record<string, { alt_text?: string }> | undefined)?.fr?.alt_text ?? '') || ''} loading="lazy" />
        ) : (
          <ImageOff aria-hidden="true" />
        )
      ) : (
        <FileText aria-hidden="true" />
      )}
    </span>
  );
}

interface MediaPickerProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  kind: MediaKindFilter;
  onSelect: (media: Item) => void;
}

/** Choix d'un média dans la bibliothèque, ou envoi d'un nouveau fichier (public par défaut). */
export function MediaPicker({ open, onOpenChange, kind, onSelect }: MediaPickerProps) {
  const [page, setPage] = useState(1);
  const filters: Record<string, string> = kind === 'any' ? {} : { kind };
  const list = useResourceList('media', { page, perPage: 24, sort: '-id', filters }, open);
  const upload = useUploadMedia();
  const input = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string | null>(null);

  const onFile = (file: File | undefined) => {
    if (!file) {
      return;
    }

    setError(null);
    upload.mutate(
      { file, visibility: 'public' },
      {
        onSuccess: (media) => {
          onSelect(media);
          onOpenChange(false);
        },
        onError: (message) => setError(String(message instanceof Error ? message.message : message)),
      },
    );
  };

  return (
    <Dialog
      open={open}
      onOpenChange={onOpenChange}
      title="Choisir un média"
      description="Cliquez sur un média, ou envoyez un nouveau fichier."
      closeLabel="Fermer"
      className={styles.pickerDialog}
    >
      <div className={styles.pickerBar}>
        <input
          ref={input}
          type="file"
          className="visually-hidden"
          accept={
            kind === 'document' ? '.pdf' : kind === 'video' ? '.mp4,.webm' : kind === 'any' ? '.jpg,.jpeg,.png,.webp,.pdf,.mp4,.webm' : '.jpg,.jpeg,.png,.webp'
          }
          onChange={(event) => onFile(event.target.files?.[0])}
        />
        <Button size="sm" onClick={() => input.current?.click()} loading={upload.isPending}>
          <Upload aria-hidden="true" />
          Envoyer un fichier
        </Button>
        {kind !== 'any' ? <Badge mono>{kind}</Badge> : null}
      </div>
      {error ? (
        <p className={styles.error} role="alert">
          {error}
        </p>
      ) : null}
      {list.isPending ? (
        <div className={styles.pickerGrid}>
          {Array.from({ length: 8 }, (_, index) => (
            <Skeleton key={index} height="6rem" />
          ))}
        </div>
      ) : list.data?.data.length ? (
        <ul className={styles.pickerGrid}>
          {list.data.data.map((media) => (
            <li key={media.id}>
              <button
                type="button"
                className={styles.pickerItem}
                onClick={() => {
                  onSelect(media);
                  onOpenChange(false);
                }}
              >
                <MediaThumb media={media} />
                <span className={styles.pickerName}>{String(media.original_name ?? media.external_url ?? `#${media.id}`)}</span>
                {media.visibility === 'private' ? <Badge tone="warning">Privé</Badge> : null}
              </button>
            </li>
          ))}
        </ul>
      ) : (
        <EmptyState title="La bibliothèque est vide." description="Envoyez un premier fichier." />
      )}
      {list.data && list.data.meta.last_page > 1 ? (
        <div className={styles.pickerPages}>
          <Button size="sm" variant="secondary" disabled={page <= 1} onClick={() => setPage(page - 1)}>
            Précédent
          </Button>
          <span>
            {page} / {list.data.meta.last_page}
          </span>
          <Button size="sm" variant="secondary" disabled={page >= list.data.meta.last_page} onClick={() => setPage(page + 1)}>
            Suivant
          </Button>
        </div>
      ) : null}
    </Dialog>
  );
}
