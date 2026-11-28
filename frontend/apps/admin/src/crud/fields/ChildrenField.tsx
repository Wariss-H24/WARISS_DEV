import { Button, Card } from '@wariss/ui';
import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-react';
import { useFieldArray, useFormContext } from 'react-hook-form';
import { childValues, type FormLocale } from '../form-values';
import type { FieldDef } from '../types';
import { FieldRenderer } from './FieldRenderer';
import styles from './fields.module.css';

/**
 * Liste d'éléments enfants (fonctionnalités d'un projet, sections d'une étude de cas…), remplacée
 * en entier à l'enregistrement. L'ordre de la liste est l'ordre d'affichage.
 */
export function ChildrenField({ field, locale }: { field: FieldDef; locale: FormLocale }) {
  const { control } = useFormContext();
  const { fields: items, append, remove, move } = useFieldArray({ control, name: field.name });
  const childFields = field.fields ?? [];

  return (
    <fieldset className={styles.children}>
      <legend className={styles.childrenLegend}>
        {field.label} <span className={styles.count}>{items.length}</span>
      </legend>
      {items.map((item, index) => (
        <Card key={item.id} className={styles.child}>
          <div className={styles.childHead}>
            <span className={styles.childIndex}>#{index + 1}</span>
            <div className={styles.childActions}>
              <Button type="button" variant="ghost" size="sm" iconOnly aria-label="Monter" disabled={index === 0} onClick={() => move(index, index - 1)}>
                <ArrowUp aria-hidden="true" />
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                iconOnly
                aria-label="Descendre"
                disabled={index === items.length - 1}
                onClick={() => move(index, index + 1)}
              >
                <ArrowDown aria-hidden="true" />
              </Button>
              <Button type="button" variant="ghost" size="sm" iconOnly aria-label={`Retirer l’élément ${index + 1}`} onClick={() => remove(index)}>
                <Trash2 aria-hidden="true" />
              </Button>
            </div>
          </div>
          <div className={styles.grid}>
            {childFields.map((childField) => (
              <FieldRenderer key={childField.name} field={childField} locale={locale} prefix={`${field.name}.${index}.`} />
            ))}
          </div>
        </Card>
      ))}
      <Button type="button" variant="secondary" size="sm" onClick={() => append(childValues(childFields))}>
        <Plus aria-hidden="true" />
        Ajouter
      </Button>
    </fieldset>
  );
}
