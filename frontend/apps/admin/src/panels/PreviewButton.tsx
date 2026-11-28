import { useMutation } from '@tanstack/react-query';
import { Button, Tooltip } from '@wariss/ui';
import { Eye } from 'lucide-react';
import { useToast } from '~/components/Toaster';
import { errorMessage, http } from '~/lib/http';

/**
 * Aperçu d'un contenu, publié ou non, avec le vrai rendu du site (D-09) : lien signé de 30 minutes,
 * ouvert dans un nouvel onglet. Permis au compte viewer (lecture).
 */
export function PreviewButton({ type, id, locale = 'fr' }: { type: string; id: number; locale?: 'fr' | 'en' }) {
  const toast = useToast();
  const preview = useMutation({
    mutationFn: async () => (await http.get<{ data: { url: string } }>(`/admin/preview-link?type=${type}&id=${id}&locale=${locale}`)).data.data.url,
  });

  const open = () => {
    // L'onglet est ouvert tout de suite (sinon le navigateur le bloque), puis dirigé vers l'aperçu.
    const tab = window.open('', '_blank');
    preview.mutate(undefined, {
      onSuccess: (url) => {
        if (tab) {
          tab.opener = null;
          tab.location.href = url;
        } else {
          window.location.href = url;
        }
      },
      onError: (error) => {
        tab?.close();
        toast.error(errorMessage(error));
      },
    });
  };

  return (
    <Tooltip content="Lien valable 30 minutes">
      <Button variant="secondary" size="sm" onClick={open} loading={preview.isPending}>
        <Eye aria-hidden="true" />
        Aperçu
      </Button>
    </Tooltip>
  );
}
