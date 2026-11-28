/**
 * Bibliothèque de médias : envoi de fichier (multipart), doublon (409) résolu en réutilisant le
 * média existant, suppression avec confirmation des usages.
 */
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import type { Item } from '~/crud/api';
import { errorMessage, http, validationErrors } from '~/lib/http';

export interface UploadInput {
  file: File;
  visibility: 'public' | 'private';
  altText?: string;
  collectionId?: number | null;
}

export function useUploadMedia() {
  const client = useQueryClient();

  return useMutation({
    mutationFn: async ({ file, visibility, altText, collectionId }: UploadInput): Promise<Item> => {
      const form = new FormData();
      form.append('file', file);
      form.append('visibility', visibility);

      if (altText) {
        form.append('translations[fr][alt_text]', altText);
      }

      if (collectionId) {
        form.append('media_collection_id', String(collectionId));
      }

      try {
        const { data } = await http.post<{ data: Item }>('/admin/media', form);

        return data.data;
      } catch (error) {
        // Fichier déjà présent : on réutilise le média existant.
        if (error instanceof AxiosError && error.response?.status === 409) {
          const existingId = (error.response.data as { existing_id?: number }).existing_id;

          if (existingId) {
            const { data } = await http.get<{ data: Item }>(`/admin/media/${existingId}`);

            return data.data;
          }
        }

        const fields = validationErrors(error);

        throw new Error(fields ? Object.values(fields).join(' ') : errorMessage(error));
      }
    },
    onSuccess: () => void client.invalidateQueries({ queryKey: ['admin', 'media'] }),
  });
}

export interface MediaUsage {
  table: string;
  column: string;
  ids: number[];
  count: number;
  blocking: boolean;
}
