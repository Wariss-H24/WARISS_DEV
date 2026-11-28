import { Badge, Button, Card, EmptyState, Skeleton } from '@wariss/ui';
import { ArrowLeft, ExternalLink, Save, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { FormProvider, useForm, type FieldErrors } from 'react-hook-form';
import { Link, Navigate, useBlocker, useNavigate, useParams } from 'react-router';
import { ConfirmDialog } from '~/components/ConfirmDialog';
import { PageTitle } from '~/components/PageTitle';
import { useToast } from '~/components/Toaster';
import { useAbilities } from '~/lib/auth';
import { formatDateTime, labelOf } from '~/lib/format';
import { errorMessage, validationErrors } from '~/lib/http';
import { MessagePanel } from '~/panels/MessagePanel';
import { PreviewButton } from '~/panels/PreviewButton';
import { ResumeVersionsPanel } from '~/panels/ResumeVersionsPanel';
import { SeoPanel } from '~/panels/SeoPanel';
import { useDeleteResource, useResourceItem, useSaveResource, type Item } from './api';
import { StatusBadge } from './cells';
import { FieldRenderer } from './fields/FieldRenderer';
import { allFields, formValues, LOCALES, payloadFrom, type FormLocale } from './form-values';
import { optionLabel, PUBLICATION_STATUS } from './options';
import { resourceOf } from './resources';
import type { ResourceConfig } from './types';
import styles from './ResourceFormPage.module.css';

const LOCALE_LABELS: Record<FormLocale, string> = { fr: 'Français', en: 'English' };

/** Nombre d'erreurs dans les champs traduits d'une langue (pastille de l'onglet). */
function errorCount(errors: FieldErrors, locale: FormLocale): number {
  const count = (node: unknown): number =>
    node && typeof node === 'object'
      ? 'message' in (node as object) && 'type' in (node as object)
        ? 1
        : Object.values(node).reduce<number>((sum, child) => sum + count(child), 0)
      : 0;
  const translations = (errors.translations as Record<string, unknown> | undefined)?.[locale];
  let total = count(translations);

  for (const value of Object.values(errors)) {
    if (Array.isArray(value)) {
      for (const child of value) {
        total += count((child as { translations?: Record<string, unknown> } | undefined)?.translations?.[locale]);
      }
    }
  }

  return total;
}

function Form({ config, item }: { config: ResourceConfig; item?: Item }) {
  const isNew = !item;
  const navigate = useNavigate();
  const toast = useToast();
  const { canWrite } = useAbilities();
  const [locale, setLocale] = useState<FormLocale>('fr');
  const [deleting, setDeleting] = useState(false);
  const methods = useForm({ defaultValues: formValues(config, item) });
  const save = useSaveResource(config.key, item?.id ?? null);
  const remove = useDeleteResource(config.key);
  const { isDirty, errors } = methods.formState;
  const readOnly = !canWrite || Boolean(config.readOnly);
  const hasTranslations = allFields(config).some((field) => field.translatable || field.fields?.some((child) => child.translatable));

  const blocker = useBlocker(({ currentLocation, nextLocation }) => isDirty && !save.isPending && currentLocation.pathname !== nextLocation.pathname);

  useEffect(() => {
    const onBeforeUnload = (event: BeforeUnloadEvent) => {
      if (isDirty) {
        event.preventDefault();
      }
    };
    window.addEventListener('beforeunload', onBeforeUnload);

    return () => window.removeEventListener('beforeunload', onBeforeUnload);
  }, [isDirty]);

  const submit = methods.handleSubmit((values) => {
    let payload: Record<string, unknown>;

    try {
      payload = payloadFrom(config, values, isNew);
    } catch {
      const jsonField = allFields(config).find((field) => field.type === 'json');
      methods.setError(jsonField?.name ?? 'root', { type: 'json', message: 'JSON invalide.' });

      return;
    }

    save.mutate(payload, {
      onSuccess: (saved) => {
        methods.reset(formValues(config, saved));
        toast.success(isNew ? `${config.feminine ? 'Créée' : 'Créé'} avec succès.` : 'Modifications enregistrées.');

        if (isNew) {
          navigate(`/r/${config.key}/${saved.id}`, { replace: true });
        }
      },
      onError: (error) => {
        const fields = validationErrors(error);

        if (!fields) {
          toast.error(errorMessage(error));

          return;
        }

        let localeWithError: FormLocale | null = null;

        for (const [path, message] of Object.entries(fields)) {
          methods.setError(path, { type: 'server', message });
          const match = /translations\.(fr|en)\./.exec(path);
          localeWithError ??= match ? (match[1] as FormLocale) : null;
        }

        if (localeWithError) {
          setLocale(localeWithError);
        }

        toast.error('Certains champs sont à corriger.');
      },
    });
  });

  const onKeyDown = useCallback(
    (event: KeyboardEvent) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
        event.preventDefault();

        if (!readOnly) {
          void submit();
        }
      }
    },
    [readOnly, submit],
  );

  useEffect(() => {
    window.addEventListener('keydown', onKeyDown);

    return () => window.removeEventListener('keydown', onKeyDown);
  }, [onKeyDown]);

  const title = isNew ? `${config.feminine ? 'Nouvelle' : 'Nouveau'} ${config.singular}` : labelOf(item);
  const publicUrl = item && config.publicPath && item.status === 'published' ? config.publicPath(item) : null;
  const hasSidePanels = !isNew && (config.contentType || config.key === 'resumes' || config.key === 'messages');

  return (
    <>
      <PageTitle
        title={title}
        badge={
          item?.status ? (
            <StatusBadge
              value={String(item.status)}
              label={optionLabel(config.columns.find((column) => column.key === 'status')?.options ?? PUBLICATION_STATUS, item.status)}
            />
          ) : undefined
        }
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: config.label, to: `/r/${config.key}` }, { label: isNew ? 'Création' : `#${item.id}` }]}
        actions={
          <>
            {publicUrl ? (
              <Button asChild variant="secondary" size="sm">
                <a href={publicUrl} target="_blank" rel="noopener noreferrer">
                  <ExternalLink aria-hidden="true" />
                  Voir sur le site
                </a>
              </Button>
            ) : null}
            {!isNew && config.contentType ? <PreviewButton type={config.contentType} id={item.id} /> : null}
          </>
        }
      />

      {readOnly && !config.readOnly ? (
        <p className={styles.readOnly} role="note">
          Compte en lecture seule : vous pouvez consulter ce contenu, pas le modifier.
        </p>
      ) : null}

      <FormProvider {...methods}>
        <div className={hasSidePanels ? styles.layout : undefined}>
          <form onSubmit={submit} noValidate className={styles.main}>
            {hasTranslations ? (
              <div className={styles.locales} role="tablist" aria-label="Langue des contenus">
                {LOCALES.map((code) => {
                  const count = errorCount(errors, code);

                  return (
                    <button key={code} type="button" role="tab" aria-selected={locale === code} className={styles.localeTab} onClick={() => setLocale(code)}>
                      {LOCALE_LABELS[code]}
                      {code === 'fr' ? <span className={styles.localeHint}>obligatoire</span> : <span className={styles.localeHint}>facultatif</span>}
                      {count ? <Badge tone="danger">{count}</Badge> : null}
                    </button>
                  );
                })}
              </div>
            ) : null}

            <fieldset disabled={readOnly} className={styles.fieldset}>
              {config.groups.map((group) => {
                const fields = group.fields.filter((field) => !field.only || field.only === (isNew ? 'create' : 'update'));

                return fields.length ? (
                  <Card key={group.title} padded className={styles.group}>
                    <div className={styles.groupHead}>
                      <h2 className={styles.groupTitle}>{group.title}</h2>
                      {group.description ? <p className={styles.groupDescription}>{group.description}</p> : null}
                    </div>
                    <div className={styles.grid}>
                      {fields.map((field) => (
                        <FieldRenderer key={field.name} field={field} locale={locale} />
                      ))}
                    </div>
                  </Card>
                ) : null;
              })}
            </fieldset>

            {errors.root?.message ? (
              <p className={styles.formError} role="alert">
                {errors.root.message}
              </p>
            ) : null}

            {readOnly ? null : (
              <div className={styles.bar}>
                <Button asChild variant="ghost">
                  <Link to={`/r/${config.key}`}>
                    <ArrowLeft aria-hidden="true" />
                    Retour à la liste
                  </Link>
                </Button>
                <span className={styles.dirty} aria-live="polite">
                  {isDirty ? 'Modifications non enregistrées' : isNew ? '' : 'Tout est enregistré'}
                </span>
                {!isNew && !config.noCreate ? (
                  <Button type="button" variant="danger" onClick={() => setDeleting(true)}>
                    <Trash2 aria-hidden="true" />
                    Supprimer
                  </Button>
                ) : null}
                <Button type="submit" loading={save.isPending} disabled={!isDirty && !isNew}>
                  <Save aria-hidden="true" />
                  Enregistrer
                  <kbd className={styles.kbd}>Ctrl S</kbd>
                </Button>
              </div>
            )}
          </form>

          {hasSidePanels && item ? (
            <aside className={styles.side}>
              {config.key === 'messages' ? <MessagePanel message={item} /> : null}
              {config.key === 'resumes' ? <ResumeVersionsPanel resume={item} /> : null}
              {config.contentType ? <SeoPanel type={config.contentType} id={item.id} /> : null}
              <Card padded className={styles.metaCard}>
                <p className="mono-label">Informations</p>
                <dl className={styles.meta}>
                  <div>
                    <dt>Créé</dt>
                    <dd>{formatDateTime(item.created_at as string | null)}</dd>
                  </div>
                  <div>
                    <dt>Modifié</dt>
                    <dd>{formatDateTime(item.updated_at as string | null)}</dd>
                  </div>
                </dl>
              </Card>
            </aside>
          ) : null}
        </div>
      </FormProvider>

      <ConfirmDialog
        open={deleting}
        onOpenChange={setDeleting}
        title={`Supprimer « ${title} » ?`}
        description={config.softDeletes ? 'Le contenu ira dans la corbeille : vous pourrez le restaurer depuis la liste.' : 'Cette action est définitive.'}
        confirmLabel="Supprimer"
        loading={remove.isPending}
        onConfirm={() =>
          item &&
          remove.mutate(
            { id: item.id },
            {
              onSuccess: () => {
                methods.reset();
                toast.success('Contenu supprimé.');
                navigate(`/r/${config.key}`, { replace: true });
              },
              onError: (error) => {
                setDeleting(false);
                toast.error(errorMessage(error));
              },
            },
          )
        }
      />

      <ConfirmDialog
        open={blocker.state === 'blocked'}
        onOpenChange={(open) => !open && blocker.reset?.()}
        title="Quitter sans enregistrer ?"
        description="Vos modifications seront perdues."
        confirmLabel="Quitter"
        onConfirm={() => blocker.proceed?.()}
      />
    </>
  );
}

/** Création (/r/:ressource/new) ou modification (/r/:ressource/:id) d'un contenu. */
export function ResourceFormPage() {
  const { resource: key, id } = useParams();
  const config = resourceOf(key);
  const { canViewSensitive } = useAbilities();
  const numericId = id && id !== 'new' ? Number(id) : null;
  const item = useResourceItem(config?.key ?? '', numericId);

  if (!config || (config.sensitive && !canViewSensitive) || (numericId === null && (config.noCreate || config.readOnly))) {
    return <Navigate to={config ? `/r/${config.key}` : '/'} replace />;
  }

  if (numericId !== null && item.isPending) {
    return (
      <div style={{ display: 'grid', gap: 'var(--space-4)' }} aria-busy="true">
        <Skeleton height="2.5rem" width="45%" />
        <Skeleton height="18rem" />
        <Skeleton height="12rem" />
      </div>
    );
  }

  if (numericId !== null && item.isError) {
    return <EmptyState title="Contenu introuvable" description={errorMessage(item.error)} action={<Link to={`/r/${config.key}`}>Retour à la liste</Link>} />;
  }

  return <Form key={`${config.key}-${numericId ?? 'new'}`} config={config} item={item.data} />;
}
