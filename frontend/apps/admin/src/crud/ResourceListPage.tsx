import { Badge, Button, Card, EmptyState, Input, Select, Skeleton, Tooltip } from '@wariss/ui';
import { ArrowDown, ArrowUp, ArrowUpDown, ChevronLeft, ChevronRight, Pencil, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Link, Navigate, useNavigate, useParams, useSearchParams } from 'react-router';
import { ConfirmDialog } from '~/components/ConfirmDialog';
import { PageTitle } from '~/components/PageTitle';
import { useToast } from '~/components/Toaster';
import { useAbilities } from '~/lib/auth';
import { labelOf } from '~/lib/format';
import { errorMessage } from '~/lib/http';
import { useDeleteResource, useResourceList, useRestoreResource, type Item } from './api';
import { renderCell } from './cells';
import { resourceOf } from './resources';
import styles from './ResourceListPage.module.css';

/**
 * Liste d'un type de contenu : filtres, recherche, tri, pagination, état mémorisé dans l'adresse
 * (partageable, retour arrière conservé), suppression et restauration.
 */
export function ResourceListPage() {
  const { resource: key } = useParams();
  const config = resourceOf(key);
  const [params, setParams] = useSearchParams();
  const navigate = useNavigate();
  const toast = useToast();
  const { canWrite, canViewSensitive } = useAbilities();
  const [toDelete, setToDelete] = useState<Item | null>(null);
  const [search, setSearch] = useState(params.get('search') ?? '');

  const page = Number(params.get('page') ?? '1') || 1;
  const sort = params.get('sort') ?? config?.defaultSort ?? '-id';
  const filters: Record<string, string> = {};

  for (const filter of config?.filters ?? []) {
    filters[filter.name] = params.get(filter.name) ?? '';
  }

  if (config?.searchable && params.get('search')) {
    filters.search = params.get('search') ?? '';
  }

  const inTrash = Boolean(config?.softDeletes && params.get('trashed') === 'only');

  if (inTrash) {
    filters.trashed = 'only';
  }

  const list = useResourceList(config?.key ?? '', { page, sort, filters }, Boolean(config));
  const remove = useDeleteResource(config?.key ?? '');
  const restore = useRestoreResource(config?.key ?? '');

  if (!config) {
    return <Navigate to="/" replace />;
  }

  if (config.sensitive && !canViewSensitive) {
    return <Navigate to="/" replace />;
  }

  const update = (changes: Record<string, string | null>) => {
    const next = new URLSearchParams(params);

    for (const [name, value] of Object.entries(changes)) {
      if (value === null || value === '') {
        next.delete(name);
      } else {
        next.set(name, value);
      }
    }

    if (!('page' in changes)) {
      next.delete('page');
    }

    setParams(next);
  };

  const toggleSort = (column: string) => update({ sort: sort === column ? `-${column}` : column });
  const editable = !config.readOnly;
  const creatable = canWrite && !config.readOnly && !config.noCreate;
  const items = list.data?.data ?? [];
  const meta = list.data?.meta;

  const confirmDelete = () => {
    if (!toDelete) {
      return;
    }

    remove.mutate(
      { id: toDelete.id },
      {
        onSuccess: () => {
          toast.success(config.softDeletes ? 'Contenu placé dans la corbeille.' : 'Contenu supprimé.');
          setToDelete(null);
        },
        onError: (error) => {
          toast.error(errorMessage(error));
          setToDelete(null);
        },
      },
    );
  };

  return (
    <>
      <PageTitle
        title={config.label}
        description={meta ? `${meta.total} élément${meta.total > 1 ? 's' : ''}` : undefined}
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: config.label }]}
        actions={
          creatable ? (
            <Button asChild>
              <Link to={`/r/${config.key}/new`}>
                <Plus aria-hidden="true" />
                {config.feminine ? 'Nouvelle' : 'Nouveau'} {config.singular}
              </Link>
            </Button>
          ) : null
        }
      />

      {config.filters?.length || config.searchable || config.softDeletes ? (
        <div className={styles.toolbar} role="search">
          {config.softDeletes ? (
            <div className={styles.segmented} role="group" aria-label="Contenus affichés">
              <button type="button" aria-pressed={!inTrash} onClick={() => update({ trashed: null })}>
                Contenus
              </button>
              <button type="button" aria-pressed={inTrash} onClick={() => update({ trashed: 'only' })}>
                <Trash2 aria-hidden="true" size={14} />
                Corbeille
              </button>
            </div>
          ) : null}
          {config.searchable ? (
            <form
              className={styles.search}
              onSubmit={(event) => {
                event.preventDefault();
                update({ search });
              }}
            >
              <Search aria-hidden="true" size={16} className={styles.searchIcon} />
              <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Rechercher…" aria-label="Rechercher" />
            </form>
          ) : null}
          {config.filters?.map((filter) => (
            <Select
              key={filter.name}
              value={filters[filter.name]}
              onChange={(event) => update({ [filter.name]: event.target.value })}
              aria-label={filter.label}
              className={styles.filter}
            >
              <option value="">{filter.label} : tous</option>
              {filter.options.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </Select>
          ))}
        </div>
      ) : null}

      <Card className={styles.tableCard}>
        {list.isPending ? (
          <div className={styles.loading} aria-busy="true">
            {Array.from({ length: 6 }, (_, index) => (
              <Skeleton key={index} height="2.75rem" />
            ))}
          </div>
        ) : list.isError ? (
          <EmptyState
            title="Impossible de charger la liste"
            description={errorMessage(list.error)}
            action={<Button onClick={() => void list.refetch()}>Réessayer</Button>}
          />
        ) : items.length === 0 ? (
          <EmptyState
            icon={<config.icon aria-hidden="true" size={22} />}
            title={Object.values(filters).some(Boolean) ? 'Aucun résultat avec ces filtres.' : `Aucun élément pour le moment.`}
            action={
              creatable ? (
                <Button asChild variant="secondary" size="sm">
                  <Link to={`/r/${config.key}/new`}>
                    <Plus aria-hidden="true" />
                    Créer le premier
                  </Link>
                </Button>
              ) : null
            }
          />
        ) : (
          <div className={styles.scroll}>
            <table className={styles.table}>
              <thead>
                <tr>
                  {config.columns.map((column) => {
                    const active = sort === column.key || sort === `-${column.key}`;

                    return (
                      <th key={column.key} scope="col" aria-sort={active ? (sort.startsWith('-') ? 'descending' : 'ascending') : undefined}>
                        {column.sortable ? (
                          <button type="button" className={styles.sortButton} onClick={() => toggleSort(column.key)}>
                            {column.label}
                            {active ? (
                              sort.startsWith('-') ? (
                                <ArrowDown aria-hidden="true" size={13} />
                              ) : (
                                <ArrowUp aria-hidden="true" size={13} />
                              )
                            ) : (
                              <ArrowUpDown aria-hidden="true" size={13} />
                            )}
                          </button>
                        ) : (
                          column.label
                        )}
                      </th>
                    );
                  })}
                  <th scope="col" className={styles.actionsHead}>
                    <span className="visually-hidden">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {items.map((item) => {
                  const trashed = Boolean(item.deleted_at);

                  return (
                    <tr
                      key={item.id}
                      data-trashed={trashed || undefined}
                      onClick={() => editable && navigate(`/r/${config.key}/${item.id}`)}
                      className={editable ? styles.clickable : undefined}
                    >
                      {config.columns.map((column, index) => (
                        <td key={column.key}>
                          {index === 0 && editable ? (
                            <Link to={`/r/${config.key}/${item.id}`} className={styles.rowLink} onClick={(event) => event.stopPropagation()}>
                              {renderCell(item, column)}
                            </Link>
                          ) : (
                            renderCell(item, column)
                          )}
                          {index === 0 && trashed ? <Badge tone="danger">Corbeille</Badge> : null}
                        </td>
                      ))}
                      <td className={styles.actions} onClick={(event) => event.stopPropagation()}>
                        {editable ? (
                          <Tooltip content={canWrite ? 'Modifier' : 'Consulter'}>
                            <Button asChild variant="ghost" size="sm" iconOnly>
                              <Link to={`/r/${config.key}/${item.id}`} aria-label={`Modifier ${labelOf(item)}`}>
                                <Pencil aria-hidden="true" />
                              </Link>
                            </Button>
                          </Tooltip>
                        ) : null}
                        {canWrite && trashed ? (
                          <Tooltip content="Restaurer">
                            <Button
                              variant="ghost"
                              size="sm"
                              iconOnly
                              aria-label={`Restaurer ${labelOf(item)}`}
                              onClick={() =>
                                restore.mutate(item.id, {
                                  onSuccess: () => toast.success('Contenu restauré.'),
                                  onError: (error) => toast.error(errorMessage(error)),
                                })
                              }
                            >
                              <RotateCcw aria-hidden="true" />
                            </Button>
                          </Tooltip>
                        ) : null}
                        {canWrite && !trashed && (!config.readOnly || config.key === 'newsletter-subscribers') ? (
                          <Tooltip content="Supprimer">
                            <Button variant="ghost" size="sm" iconOnly aria-label={`Supprimer ${labelOf(item)}`} onClick={() => setToDelete(item)}>
                              <Trash2 aria-hidden="true" />
                            </Button>
                          </Tooltip>
                        ) : null}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </Card>

      {meta && meta.last_page > 1 ? (
        <nav className={styles.pagination} aria-label="Pagination">
          <Button variant="secondary" size="sm" disabled={page <= 1} onClick={() => update({ page: String(page - 1) })}>
            <ChevronLeft aria-hidden="true" />
            Précédent
          </Button>
          <span>
            Page {meta.current_page} sur {meta.last_page}
          </span>
          <Button variant="secondary" size="sm" disabled={page >= meta.last_page} onClick={() => update({ page: String(page + 1) })}>
            Suivant
            <ChevronRight aria-hidden="true" />
          </Button>
        </nav>
      ) : null}

      <ConfirmDialog
        open={toDelete !== null}
        onOpenChange={(open) => !open && setToDelete(null)}
        title={`Supprimer « ${labelOf(toDelete)} » ?`}
        description={config.softDeletes ? 'Le contenu ira dans la corbeille : vous pourrez le restaurer.' : 'Cette action est définitive.'}
        confirmLabel="Supprimer"
        loading={remove.isPending}
        onConfirm={confirmDelete}
      />
    </>
  );
}
