import { Textarea } from '@wariss/ui';
import { Eye, PencilLine } from 'lucide-react';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';
import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';
import styles from './fields.module.css';

/**
 * Éditeur Markdown (stockage en Markdown, comme le site public) : écriture et aperçu nettoyé.
 */
export function MarkdownField({ path }: { path: string }) {
  const { register, watch } = useFormContext();
  const [mode, setMode] = useState<'write' | 'preview'>('write');
  const value = (watch(path) as string | null) ?? '';
  const words = value.trim() ? value.trim().split(/\s+/).length : 0;

  return (
    <div className={styles.markdown}>
      <div className={styles.markdownBar} role="tablist" aria-label="Mode d’édition">
        <button type="button" role="tab" aria-selected={mode === 'write'} className={styles.markdownTab} onClick={() => setMode('write')}>
          <PencilLine aria-hidden="true" size={14} />
          Écrire
        </button>
        <button type="button" role="tab" aria-selected={mode === 'preview'} className={styles.markdownTab} onClick={() => setMode('preview')}>
          <Eye aria-hidden="true" size={14} />
          Aperçu
        </button>
        <span className={styles.markdownCount}>
          {words} mot{words > 1 ? 's' : ''} · Markdown
        </span>
      </div>
      {mode === 'write' ? (
        <Textarea rows={12} {...register(path)} className={styles.markdownInput} spellCheck />
      ) : (
        <div className={`prose ${styles.markdownPreview}`}>
          {value ? (
            <ReactMarkdown remarkPlugins={[remarkGfm]} rehypePlugins={[rehypeSanitize]}>
              {value}
            </ReactMarkdown>
          ) : (
            <p className={styles.muted}>Rien à afficher.</p>
          )}
        </div>
      )}
    </div>
  );
}
