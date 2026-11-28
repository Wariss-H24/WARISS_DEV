import { CircleExclamationPoint, Check } from 'lucide-react';
import { Checkbox as RadixCheckbox } from 'radix-ui';
import {
  cloneElement,
  isValidElement,
  useId,
  type InputHTMLAttributes,
  type ReactElement,
  type ReactNode,
  type Ref,
  type SelectHTMLAttributes,
  type TextareaHTMLAttributes,
} from 'react';
import { cx } from '../../lib/cx';
import styles from './Form.module.css';

export interface FieldProps {
  label: ReactNode;
  /** Aide sous le champ. */
  hint?: ReactNode;
  /** Message d'erreur : marque le contrôle invalide et l'annonce aux lecteurs d'écran. */
  error?: ReactNode;
  required?: boolean;
  /** Un seul contrôle (Input, Textarea…), relié automatiquement au libellé, à l'aide et à l'erreur. */
  children: ReactElement<{ id?: string; 'aria-describedby'?: string; 'aria-invalid'?: boolean; required?: boolean }>;
  className?: string;
}

/**
 * Champ de formulaire accessible : libellé associé, aide et erreur reliées par aria-describedby,
 * état invalide transmis au contrôle.
 */
export function Field({ label, hint, error, required = false, children, className }: FieldProps) {
  const generatedId = useId();
  const controlId = children.props.id ?? generatedId;
  const hintId = hint ? `${controlId}-hint` : undefined;
  const errorId = error ? `${controlId}-error` : undefined;
  const describedBy = [children.props['aria-describedby'], hintId, errorId].filter(Boolean).join(' ') || undefined;

  return (
    <div className={cx(styles.field, className)}>
      <label htmlFor={controlId} className={styles.label}>
        {label}
        {required ? (
          <span className={styles.required} aria-hidden="true">
            *
          </span>
        ) : null}
      </label>
      {isValidElement(children)
        ? cloneElement(children, {
            id: controlId,
            'aria-describedby': describedBy,
            'aria-invalid': error ? true : undefined,
            required: required || undefined,
          })
        : children}
      {hint ? (
        <p id={hintId} className={styles.hint}>
          {hint}
        </p>
      ) : null}
      {error ? (
        <p id={errorId} className={styles.error} role="alert">
          <CircleExclamationPoint aria-hidden="true" size={14} />
          {error}
        </p>
      ) : null}
    </div>
  );
}

export function Input({ className, ref, ...props }: InputHTMLAttributes<HTMLInputElement> & { ref?: Ref<HTMLInputElement> }) {
  return <input ref={ref} className={cx(styles.control, className)} {...props} />;
}

export function Textarea({ className, ref, ...props }: TextareaHTMLAttributes<HTMLTextAreaElement> & { ref?: Ref<HTMLTextAreaElement> }) {
  return <textarea ref={ref} className={cx(styles.control, className)} {...props} />;
}

/** Liste déroulante native (accessible et adaptée au mobile sans effort). */
export function Select({ className, ref, ...props }: SelectHTMLAttributes<HTMLSelectElement> & { ref?: Ref<HTMLSelectElement> }) {
  return <select ref={ref} className={cx(styles.control, styles.select, className)} {...props} />;
}

export interface CheckboxProps {
  label: ReactNode;
  checked?: boolean;
  onCheckedChange?: (checked: boolean) => void;
  name?: string;
  required?: boolean;
  invalid?: boolean;
  id?: string;
  className?: string;
}

/** Case à cocher accessible (Radix) avec son libellé cliquable. */
export function Checkbox({ label, checked, onCheckedChange, name, required, invalid, id, className }: CheckboxProps) {
  const generatedId = useId();
  const checkboxId = id ?? generatedId;

  return (
    <label htmlFor={checkboxId} className={cx(styles.checkboxRow, className)}>
      <RadixCheckbox.Root
        id={checkboxId}
        name={name}
        required={required}
        checked={checked}
        onCheckedChange={(value) => onCheckedChange?.(value === true)}
        aria-invalid={invalid || undefined}
        className={styles.checkbox}
      >
        <RadixCheckbox.Indicator>
          <Check aria-hidden="true" strokeWidth={3} />
        </RadixCheckbox.Indicator>
      </RadixCheckbox.Root>
      <span>{label}</span>
    </label>
  );
}
