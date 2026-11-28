/**
 * Accès générique aux ressources de l'administration (/api/v1/admin/{ressource}), avec
 * TanStack Query : cache par ressource, invalidation après chaque écriture.
 */
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { http } from '~/lib/http';

export type Item = Record<string, unknown> & { id: number };

export interface ListResponse {
  data: Item[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

export interface ListParams {
  page?: number;
  perPage?: number;
  sort?: string;
  filters?: Record<string, string>;
}

export function listQueryString({ page = 1, perPage = 20, sort, filters = {} }: ListParams): string {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) });

  if (sort) {
    params.set('sort', sort);
  }

  for (const [name, value] of Object.entries(filters)) {
    if (value !== '') {
      params.set(`filter[${name}]`, value);
    }
  }

  return params.toString();
}

export function useResourceList(resource: string, params: ListParams, enabled = true) {
  return useQuery({
    queryKey: ['admin', resource, 'list', params],
    queryFn: async () => (await http.get<ListResponse>(`/admin/${resource}?${listQueryString(params)}`)).data,
    placeholderData: keepPreviousData,
    enabled,
  });
}

/** Tous les éléments d'une ressource (listes de choix des relations), jusqu'à 100. */
export function useResourceOptions(resource: string | undefined) {
  return useQuery({
    queryKey: ['admin', resource, 'options'],
    queryFn: async () => (await http.get<ListResponse>(`/admin/${resource}?per_page=100`)).data.data,
    enabled: Boolean(resource),
    staleTime: 60_000,
  });
}

export function useResourceItem(resource: string, id: number | null) {
  return useQuery({
    queryKey: ['admin', resource, 'item', id],
    queryFn: async () => (await http.get<{ data: Item }>(`/admin/${resource}/${id}`)).data.data,
    enabled: id !== null,
  });
}

export function useInvalidate(resource: string) {
  const client = useQueryClient();

  return () => client.invalidateQueries({ queryKey: ['admin', resource] });
}

export function useSaveResource(resource: string, id: number | null) {
  const invalidate = useInvalidate(resource);
  const client = useQueryClient();

  return useMutation({
    mutationFn: async (payload: Record<string, unknown>) => {
      const response =
        id === null ? await http.post<{ data: Item }>(`/admin/${resource}`, payload) : await http.patch<{ data: Item }>(`/admin/${resource}/${id}`, payload);

      return response.data.data;
    },
    onSuccess: (item) => {
      client.setQueryData(['admin', resource, 'item', item.id], item);
      void invalidate();
      void client.invalidateQueries({ queryKey: ['admin', 'dashboard'] });
    },
  });
}

export function useDeleteResource(resource: string) {
  const invalidate = useInvalidate(resource);

  return useMutation({
    mutationFn: ({ id, force = false, query = '' }: { id: number; force?: boolean; query?: string }) =>
      http.delete(`/admin/${resource}/${id}${force ? '/force' : ''}${query}`),
    onSuccess: () => void invalidate(),
  });
}

export function useRestoreResource(resource: string) {
  const invalidate = useInvalidate(resource);

  return useMutation({
    mutationFn: (id: number) => http.post(`/admin/${resource}/${id}/restore`),
    onSuccess: () => void invalidate(),
  });
}
