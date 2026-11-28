import { SITE_URL } from '~/lib/api.server';

/** /robots.txt : tout est indexable sauf les aperçus de brouillons. */
export function loader() {
  const body = ['User-agent: *', 'Allow: /', 'Disallow: /*/preview/', '', `Sitemap: ${SITE_URL}/sitemap.xml`, ''].join('\n');

  return new Response(body, {
    headers: { 'Content-Type': 'text/plain; charset=UTF-8', 'Cache-Control': 'public, max-age=86400' },
  });
}
