import type { Category, Paginated, Post, Tag } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { useTranslation } from 'react-i18next';
import { data, useLoaderData } from 'react-router';
import type { Route } from './+types/blog';
import { CategoryFilter } from '~/components/CategoryFilter/CategoryFilter';
import { PostCard } from '~/features/content/cards';
import { ListingPage } from '~/features/content/ListingPage';
import { apiGet, apiGetOr } from '~/lib/api.server';
import { layoutSite, pageParam, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { pathTo } from '~/lib/routes';
import { buildMeta, staticPaths } from '~/lib/seo';

/**
 * Blog : liste générale, par catégorie (/blog/category/:category) ou par étiquette (/blog/tag/:tag).
 * Une même route, trois adresses (déclarées avec des identifiants distincts dans routes.ts).
 */
export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const category = 'category' in params ? (params.category as string) : undefined;
  const tag = 'tag' in params ? (params.tag as string) : undefined;

  const [posts, categories, tags] = await Promise.all([
    apiGet<Paginated<Post>>('/posts', locale, { page: pageParam(request), per_page: 12, category, tag }),
    apiGetOr<{ data: Category[] }>('/taxonomies/post-categories', locale, { data: [] }),
    tag ? apiGetOr<{ data: Tag[] }>('/taxonomies/tags', locale, { data: [] }) : Promise.resolve({ data: [] as Tag[] }),
  ]);

  const activeCategory = category ? categories.data.find((item) => item.slug === category) : undefined;
  const activeTag = tag ? tags.data.find((item) => item.slug === tag) : undefined;

  if ((category && !activeCategory) || (tag && !activeTag)) {
    throw data({ reason: 'not-found' }, { status: 404 });
  }

  const segment = activeCategory ? `blog/category` : activeTag ? `blog/tag` : null;
  const alternates = segment
    ? Object.fromEntries(
        Object.entries((activeCategory ?? activeTag)?.slugs ?? {})
          .filter(([, slug]) => slug)
          .map(([code, slug]) => [code, `/${code}/${segment}/${slug}`]),
      )
    : undefined;

  return { locale, posts, categories: categories.data, activeCategory, activeTag, alternates };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;
  const filter = loaderData?.activeCategory?.name ?? loaderData?.activeTag?.name;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: filter ? `${filter} · ${t('pages.blogTitle')}` : t('pages.blogTitle'),
    description: loaderData?.activeCategory?.description ?? t('pages.blogDescription'),
    paths: (loaderData?.alternates as Record<string, string> | undefined) ?? staticPaths('blog'),
  });
}

export default function Blog() {
  const { locale, posts, categories, activeCategory, activeTag } = useLoaderData<typeof loader>();
  const { t } = useTranslation();
  const base = pathTo(locale, 'blog');

  return (
    <ListingPage
      eyebrow={t('pages.blogEyebrow')}
      title={activeCategory?.name ?? (activeTag ? `#${activeTag.name}` : t('pages.blogTitle'))}
      description={activeCategory?.description ?? t('pages.blogDescription')}
      filter={<CategoryFilter categories={categories} active={activeCategory?.slug} hrefFor={(slug) => (slug ? `${base}/category/${slug}` : base)} />}
      cards={posts.data.map((post) => (
        <PostCard key={post.id} post={post} locale={locale} headingLevel="h2" />
      ))}
      meta={posts.meta}
      clearHref={activeCategory || activeTag ? base : null}
    />
  );
}
