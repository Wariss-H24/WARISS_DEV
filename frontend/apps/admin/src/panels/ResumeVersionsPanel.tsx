import { useMutation, useQueryClient } from '@tanstack/react-query';
import { Badge, Button, Card, Checkbox, Input } from '@wariss/ui';
import { FileUp, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { useToast } from '~/components/Toaster';
import type { Item } from '~/crud/api';
import { useAbilities } from '~/lib/auth';
import { formatBytes, formatDate } from '~/lib/format';
import { API_ORIGIN, errorMessage, http, validationErrors } from '~/lib/http';
import styles from './panels.module.css';

interface Version {
  id: number;
  version_number: number;
  change_note: string | null;
  created_at: string;
  media_id: number;
  media?: { size_bytes?: number; original_name?: string } | null;
}

/** Versions d'un CV (D15) : envoi d'un PDF privé, version courante, suppression protégée. */
export function ResumeVersionsPanel({ resume }: { resume: Item }) {
  const client = useQueryClient();
  const toast = useToast();
  const { canWrite } = useAbilities();
  const input = useRef<HTMLInputElement>(null);
  const [note, setNote] = useState('');
  const [makeCurrent, setMakeCurrent] = useState(true);
  const versions = ((resume.versions as Version[] | undefined) ?? []).slice().sort((a, b) => b.version_number - a.version_number);
  const refresh = () => void client.invalidateQueries({ queryKey: ['admin', 'resumes'] });

  const upload = useMutation({
    mutationFn: (file: File) => {
      const form = new FormData();
      form.append('file', file);
      form.append('make_current', makeCurrent ? '1' : '0');

      if (note) {
        form.append('change_note', note);
      }

      return http.post(`/admin/resumes/${resume.id}/versions`, form);
    },
    onSuccess: () => {
      toast.success('Nouvelle version enregistrée.');
      setNote('');
      refresh();
    },
    onError: (error) => {
      const fields = validationErrors(error);
      toast.error(fields ? Object.values(fields).join(' ') : errorMessage(error));
    },
  });

  const remove = useMutation({
    mutationFn: (versionId: number) => http.delete(`/admin/resumes/${resume.id}/versions/${versionId}`),
    onSuccess: () => {
      toast.success('Version supprimée.');
      refresh();
    },
    onError: (error) => toast.error(errorMessage(error)),
  });

  return (
    <Card padded className={styles.panel}>
      <div className={styles.panelHead}>
        <FileUp aria-hidden="true" size={16} />
        <h2 className={styles.panelTitle}>Versions (PDF)</h2>
      </div>
      {versions.length ? (
        <ul className={styles.versions}>
          {versions.map((version) => (
            <li key={version.id} className={styles.version}>
              <div>
                <p className={styles.versionTitle}>
                  Version {version.version_number}
                  {resume.current_version_id === version.id ? <Badge tone="success">Courante</Badge> : null}
                </p>
                <p className={styles.versionMeta}>
                  {formatDate(version.created_at)} · {formatBytes(version.media?.size_bytes)}
                  {version.change_note ? ` · ${version.change_note}` : ''}
                </p>
                <a href={`${API_ORIGIN}/api/v1/admin/media/${version.media_id}/file`} target="_blank" rel="noopener noreferrer" className={styles.link}>
                  Ouvrir le PDF
                </a>
              </div>
              {canWrite ? (
                <Button
                  variant="ghost"
                  size="sm"
                  iconOnly
                  aria-label={`Supprimer la version ${version.version_number}`}
                  onClick={() => remove.mutate(version.id)}
                  loading={remove.isPending && remove.variables === version.id}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              ) : null}
            </li>
          ))}
        </ul>
      ) : (
        <p className={styles.muted}>Aucune version : envoyez le PDF du CV.</p>
      )}
      {canWrite ? (
        <div className={styles.stack}>
          <Input value={note} onChange={(event) => setNote(event.target.value)} placeholder="Note de version (facultatif)" aria-label="Note de version" />
          <Checkbox label="Devient la version courante" checked={makeCurrent} onCheckedChange={setMakeCurrent} />
          <input
            ref={input}
            type="file"
            accept=".pdf,application/pdf"
            className="visually-hidden"
            onChange={(event) => event.target.files?.[0] && upload.mutate(event.target.files[0])}
          />
          <Button size="sm" onClick={() => input.current?.click()} loading={upload.isPending}>
            <FileUp aria-hidden="true" />
            Envoyer un PDF
          </Button>
        </div>
      ) : null}
    </Card>
  );
}
