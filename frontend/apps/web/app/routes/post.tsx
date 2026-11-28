import type { Post } from '@wariss/api-client';
import { useLoaderData } from 'react-router';
import type { Route } from './+types/post';
import { ReadingProgress } from '~/components/ReadingProgress/ReadingProgress';
import { PostView } from '~/features/content/views';
import { loadDetail } from '~/lib/detail.server';
import { layoutSite, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta } from '~/lib/seo';

export function loader({ params }: Route.LoaderArgs) {
  return loadDetail<Post>('posts', 'blog', 'post', params);
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  if (!loaderData) {
    return [];
  }

  const { item, locale, alternates } = loaderData;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: item.title,
    description: item.excerpt,
    paths: alternates,
    image: item.cover,
    type: 'article',
    publishedAt: item.published_at,
    seo: item.seo,
  });
}

export default function PostPage() {
  const { item, locale } = useLoaderData<typeof loader>();

  return (
    <>
      <ReadingProgress />
      <PostView post={item} locale={locale} />
    </>
  );
}
