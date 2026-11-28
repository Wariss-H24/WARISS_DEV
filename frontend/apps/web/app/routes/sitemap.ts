import { apiFetch } from '~/lib/api.server';

/** /sitemap.xml : généré par l'API depuis la base (contenus publiés, hreflang), relayé tel quel. */
export async function loader() {
  try {
    const response = await apiFetch('/sitemap.xml', { headers: { Accept: 'application/xml' } });

    if (!response.ok) {
      return new Response('Sitemap indisponible', { status: 503 });
    }

    return new Response(await response.text(), {
      headers: {
        'Content-Type': 'application/xml; charset=UTF-8',
        'Cache-Control': 'public, max-age=3600',
      },
    });
  } catch {
    return new Response('Sitemap indisponible', { status: 503 });
  }
}
