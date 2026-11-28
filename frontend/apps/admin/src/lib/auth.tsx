/**
 * Session de l'administration : compte connecté (GET /auth/me), connexion, déconnexion.
 * Les droits viennent de l'API (abilities) ; les masquer dans l'interface n'est qu'un confort,
 * le serveur refuse de toute façon (EnsureCanWrite, gate view-sensitive-data).
 */
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { ensureCsrfCookie, http } from './http';

export interface CurrentUser {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'viewer';
  abilities: { can_write: boolean; can_view_sensitive_data: boolean };
  last_login_at: string | null;
}

export const ME_KEY = ['auth', 'me'] as const;

export function useCurrentUser() {
  return useQuery({
    queryKey: ME_KEY,
    queryFn: async (): Promise<CurrentUser | null> => {
      try {
        const { data } = await http.get<{ data: CurrentUser }>('/auth/me');

        return data.data;
      } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 401) {
          return null;
        }

        throw error;
      }
    },
    staleTime: 5 * 60_000,
    retry: false,
  });
}

export function useLogin() {
  const client = useQueryClient();

  return useMutation({
    mutationFn: async (credentials: { email: string; password: string; remember: boolean }) => {
      await ensureCsrfCookie();
      const { data } = await http.post<{ data: CurrentUser }>('/auth/session', credentials);

      return data.data;
    },
    onSuccess: (user) => client.setQueryData(ME_KEY, user),
  });
}

export function useLogout() {
  const client = useQueryClient();

  return useMutation({
    mutationFn: () => http.delete('/auth/session'),
    onSettled: () => {
      client.clear();
      client.setQueryData(ME_KEY, null);
    },
  });
}

/** Droits du compte connecté (faux tant qu'il n'est pas chargé). */
export function useAbilities() {
  const { data } = useCurrentUser();

  return { canWrite: data?.abilities.can_write ?? false, canViewSensitive: data?.abilities.can_view_sensitive_data ?? false };
}
