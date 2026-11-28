import { index, route, type RouteConfig } from '@react-router/dev/routes';

/*
 * Routes publiques (cahier des charges §21.2 bis, D-03, D-19) : tout est préfixé par la langue,
 * segments en anglais identiques dans les deux langues, slugs de contenu traduits.
 */
export default [
  index('routes/language-redirect.tsx'),
  route('sitemap.xml', 'routes/sitemap.ts'),
  route('robots.txt', 'routes/robots.ts'),

  route(':lang', 'routes/lang-layout.tsx', [
    index('routes/home.tsx'),
    route('about', 'routes/about.tsx'),
    route('skills', 'routes/skills.tsx'),
    route('experience', 'routes/experience.tsx'),
    route('projects', 'routes/projects.tsx'),
    route('projects/:slug', 'routes/project.tsx'),
    route('case-studies/:slug', 'routes/case-study.tsx'),
    route('services', 'routes/services.tsx'),
    route('services/:slug', 'routes/service.tsx'),
    route('lab', 'routes/lab.tsx'),
    route('lab/:slug', 'routes/experiment.tsx'),
    route('blog', 'routes/blog.tsx', { id: 'blog' }),
    route('blog/category/:category', 'routes/blog.tsx', { id: 'blog-category' }),
    route('blog/tag/:tag', 'routes/blog.tsx', { id: 'blog-tag' }),
    route('blog/:slug', 'routes/post.tsx'),
    route('achievements', 'routes/achievements.tsx'),
    route('testimonials', 'routes/testimonials.tsx'),
    route('resume', 'routes/resume.tsx'),
    route('contact', 'routes/contact.tsx'),
    route('terminal', 'routes/terminal.tsx'),
    route('newsletter/confirm', 'routes/newsletter-confirm.tsx'),
    route('newsletter/unsubscribe', 'routes/newsletter-unsubscribe.tsx'),
    route('preview/:token', 'routes/preview.tsx'),
    route('pages/:slug', 'routes/custom-page.tsx'),
    // Pages système éditées dans l'administration (now, uses, legal-notice, privacy…)
    route(':page', 'routes/system-page.tsx'),
    route('*', 'routes/not-found.tsx'),
  ]),

  route('*', 'routes/fallback.tsx'),
] satisfies RouteConfig;
