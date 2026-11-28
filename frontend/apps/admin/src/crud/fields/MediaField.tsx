import { Button } from '@wariss/ui';
import { ImagePlus, X } from 'lucide-react';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { MediaPicker, MediaThumb, type MediaKindFilter } from '~/media/MediaPicker';
import { useResourceItem } from '../api';
import styles from './fields.module.css';

function Selected({ id }: { id: number }) {
  const media = useResourceItem('media', id);

  if (!media.data) {
    return <span className={styles.muted}>Média #{id}</span>;
  }

  return (
    <span className={styles.mediaSelected}>
      <MediaThumb media={media.data} size="sm" />
      <span className={styles.mediaName}>{String(media.data.original_name ?? media.data.external_url ?? `#${id}`)}</span>
    </span>
  );
}

/** Référence à un média de la bibliothèque (couverture, logo, photo…). */
export function MediaField({ path, kind }: { path: string; kind: MediaKindFilter }) {
  const { control } = useFormContext();
  const [open, setOpen] = useState(false);

  return (
    <Controller
      control={control}
      name={path}
      render={({ field }) => (
        <div className={styles.media}>
          {field.value ? <Selected id={Number(field.value)} /> : <span className={styles.muted}>Aucun média</span>}
          <div className={styles.mediaActions}>
            <Button type="button" variant="secondary" size="sm" onClick={() => setOpen(true)}>
              <ImagePlus aria-hidden="true" />
              {field.value ? 'Changer' : 'Choisir'}
            </Button>
            {field.value ? (
              <Button type="button" variant="ghost" size="sm" iconOnly aria-label="Retirer le média" onClick={() => field.onChange(null)}>
                <X aria-hidden="true" />
              </Button>
            ) : null}
          </div>
          <MediaPicker open={open} onOpenChange={setOpen} kind={kind} onSelect={(media) => field.onChange(media.id)} />
        </div>
      )}
    />
  );
}
