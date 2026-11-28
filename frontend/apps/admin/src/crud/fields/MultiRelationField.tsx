import { Input } from '@wariss/ui';
import { Check, X } from 'lucide-react';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { labelOf } from '~/lib/format';
import { useResourceOptions } from '../api';
import type { FieldDef } from '../types';
import styles from './fields.module.css';

/** Sélection multiple (technologies, compétences, étiquettes) : éléments choisis en pastilles, liste filtrable. */
export function MultiRelationField({ field, path }: { field: FieldDef; path: string }) {
  const { control } = useFormContext();
  const options = useResourceOptions(field.resource);
  const [filter, setFilter] = useState('');

  return (
    <Controller
      control={control}
      name={path}
      render={({ field: control }) => {
        const selected = new Set<number>((control.value as number[] | undefined) ?? []);
        const items = options.data ?? [];
        const toggle = (id: number) => {
          const next = new Set(selected);

          if (next.has(id)) {
            next.delete(id);
          } else {
            next.add(id);
          }

          control.onChange([...next]);
        };
        const visible = items.filter((item) => labelOf(item).toLowerCase().includes(filter.toLowerCase()));

        return (
          <div className={styles.multi}>
            {selected.size ? (
              <ul className={styles.chips} aria-label="Éléments choisis">
                {items
                  .filter((item) => selected.has(item.id))
                  .map((item) => (
                    <li key={item.id}>
                      <button type="button" className={styles.chip} onClick={() => toggle(item.id)} aria-label={`Retirer ${labelOf(item)}`}>
                        {labelOf(item)}
                        <X aria-hidden="true" size={12} />
                      </button>
                    </li>
                  ))}
              </ul>
            ) : null}
            <Input
              value={filter}
              onChange={(event) => setFilter(event.target.value)}
              placeholder={options.isPending ? 'Chargement…' : `Filtrer (${items.length})`}
              aria-label={`Filtrer : ${field.label}`}
            />
            <ul className={styles.optionList} role="listbox" aria-multiselectable="true" aria-label={field.label}>
              {visible.length === 0 ? <li className={styles.muted}>Aucun élément.</li> : null}
              {visible.map((item) => (
                <li key={item.id} role="option" aria-selected={selected.has(item.id)}>
                  <button type="button" className={styles.option} onClick={() => toggle(item.id)}>
                    <span className={styles.optionCheck} data-checked={selected.has(item.id) || undefined}>
                      {selected.has(item.id) ? <Check aria-hidden="true" size={12} /> : null}
                    </span>
                    {labelOf(item)}
                  </button>
                </li>
              ))}
            </ul>
          </div>
        );
      }}
    />
  );
}
