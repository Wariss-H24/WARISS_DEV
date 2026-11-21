# WARISS.DEV — Architecture technique : backend, frontend et dépendances

Nov 21, 2026 · @Wariss BADA

Ce document fixe l'architecture de l'API Laravel et des deux interfaces React, revoit ton document frontend avec des améliorations argumentées, et liste les dépendances à installer. Il s'appuie sur la structure de base de données déjà rédigée.

## 1. Décisions validées

- **Une catégorie par projet et par article** : colonnes `project_category_id` et `post_category_id` (la table `posts` correspond à tes « articles »), aucune table pivot.
- **Conservation** : messages 24 mois, journal d'activité 12 mois, analytics bruts 90 jours ; statistiques agrégées conservées sans donnée identifiante. Des tâches planifiées Laravel purgent les données expirées.
- **Pas de commentaires publics** : ni table, ni modération. Les retours passent par le formulaire de contact.
- **Français et anglais** : URLs `/fr/...` et `/en/...`, tables de traduction, SEO dans les deux langues, onglets FR/EN dans l'administration.

## 2. Vue d'ensemble

Trois applications, un seul dépôt de code par côté (API et frontend) :

- **API Laravel** (`api.wariss.dev`) : REST versionnée, PostgreSQL, Redis, files d'attente.
- **Site public** (`wariss.dev`) : React avec rendu serveur, lecture seule via l'API publique.
- **Administration** (`admin.wariss.dev`) : application React privée (SPA), authentification par cookie Sanctum.

Les trois partagent un domaine parent, ce qui permet l'authentification Sanctum en mode SPA sans jeton à stocker dans le navigateur.

## 3. Backend Laravel

### 3.1 Principes

- On garde les conventions de Laravel (outils artisan, documentation, recrutement futur) et on regroupe **par domaine à l'intérieur des dossiers standards** : `Models/Portfolio`, `Http/Controllers/Api/V1/Admin/Portfolio`, etc.
- **Contrôleurs fins** : validation dans des `FormRequest`, logique d'écriture dans des classes `Actions` (ex. `PublishProject`), sortie JSON par des `API Resources` distinctes pour le public et l'admin.
- **Autorisations** par Policies, jamais seulement côté React.
- **Langue** : `config/locales.php` (fr, en, repli fr) ; un middleware `SetLocale` lit `?locale=` ou `Accept-Language`. Les routes admin renvoient toutes les traductions d'un contenu pour remplir les onglets FR/EN.
- **Slugs alternatifs** : chaque ressource publique renvoie les slugs des deux langues, pour que le sélecteur de langue pointe vers la bonne URL.

### 3.2 Arborescence

```text
wariss-dev-api/
├── app/
│   ├── Actions/<Domaine>/          # PublishProject, UploadMedia, SubmitMessage…
│   ├── Console/Commands/           # purges, agrégation analytics, publication programmée
│   ├── Enums/                      # PublicationStatus, UserRole, MessageStatus…
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── Public/<Domaine>/
│   │   │   ├── Admin/<Domaine>/
│   │   │   └── Auth/
│   │   ├── Middleware/             # SetLocale, EnsureCanWrite, TrackPageView
│   │   ├── Requests/Admin/<Domaine>/
│   │   └── Resources/{Public,Admin}/<Domaine>/
│   ├── Jobs/                       # images, e-mails, newsletters
│   ├── Models/<Domaine>/           # Identity, Settings, Pages, Services, Catalog,
│   │                               # Portfolio, Career, Blog, Lab, Communication,
│   │                               # Media, Resume, Seo, Analytics
│   ├── Observers/                  # cache, journal d'activité
│   ├── Policies/
│   ├── Providers/                  # morph map, rate limiters
│   ├── Services/                   # Media, Seo, Analytics, Markdown, Newsletter
│   └── Support/                    # traits HasPublicationStatus, HasSeo, HasMedia
├── bootstrap/app.php               # routes, middleware, gestion des exceptions
├── config/                         # locales, media, retention, cors, sanctum
├── database/{migrations,factories,seeders}
├── routes/
│   ├── api/v1/{public,admin,auth}.php
│   └── console.php                 # planification
├── tests/{Feature,Unit}/
└── .env.example
```

### 3.3 Groupes de routes

| Groupe | Préfixe | Accès | Usage |
| --- | --- | --- | --- |
| Public | `/api/v1/public` | Libre, limité en débit | Lecture des contenus publiés, formulaires de contact et newsletter, suivi des vues |
| Auth | `/api/v1/auth` | Libre puis session | Connexion, déconnexion, mot de passe oublié, profil courant |
| Admin | `/api/v1/admin` | Session Sanctum | CRUD complet ; le rôle `viewer` n'a accès qu'aux requêtes GET |

### 3.4 Rôles

- Colonne `role` (admin, viewer) sur `users`. Un middleware `EnsureCanWrite` refuse tout sauf GET au `viewer`, et les Policies masquent pour lui les messages, abonnés, journal d'activité, comptes et réglages sensibles.
- L'API expose l'endpoint `me` avec le rôle ; l'admin React en déduit les boutons à masquer (confort uniquement).

### 3.5 Tâches planifiées

- Publication des contenus programmés (chaque minute).
- Agrégation quotidienne des analytics, puis purge des données brutes de plus de 90 jours.
- Purge des messages de plus de 24 mois et du journal d'activité de plus de 12 mois.
- Sauvegarde quotidienne de la base et des fichiers, avec test de restauration périodique.

### 3.6 Sécurité

- Cookie de session et protection CSRF (Sanctum SPA), CORS limité aux deux domaines front.
- Limitation de débit : connexion, contact, newsletter, suivi analytics.
- Formulaires publics : Cloudflare Turnstile (vérifié côté serveur) + champ piège anti-robots.
- Téléversements : liste blanche d'extensions, vérification du vrai type MIME, renommage, suppression des données EXIF, taille maximale, disque privé pour les fichiers non publics.
- Contenu Markdown : nettoyé à l'affichage ; en-têtes de sécurité (CSP, HSTS) ; adresses IP hachées dans les messages.
- Journal d'activité sans mot de passe ni secret ; secrets uniquement dans `.env`.

### 3.7 Performance

- Cache Redis des réponses publiques par langue, invalidé par les Observers à chaque publication.
- Chargement anticipé des relations (éviter les requêtes N+1), pagination partout, index déjà prévus dans le modèle.
- Redimensionnement d'images et e-mails en file d'attente.

## 4. Frontend : revue de ton document

### 4.1 Conservé tel quel

React et TypeScript, Vite, React Router, CSS Modules et variables CSS, mobile first, Lucide React, direction « Premium Tech », palette, polices Inter / Space Grotesk / JetBrains Mono, thème sombre par défaut en public et clair en administration, conventions de nommage et de qualité. Tout cela est cohérent et reste la base.

### 4.2 Améliorations proposées

| Point | Problème | Amélioration |
| --- | --- | --- |
| SEO du site public | Une application React côté navigateur seulement envoie des balises vides aux robots de partage (LinkedIn, WhatsApp) et indexe moins bien le contenu dynamique | Site public en **React Router v7 mode framework** (basé sur Vite, rendu serveur) ; l'administration reste une SPA |
| Deux applications mélangées | Un seul `src/` livre aux visiteurs le poids de l'admin (éditeur, graphiques) | **Monorepo pnpm** : `apps/web`, `apps/admin`, `packages/ui`, `packages/api-client`, `packages/i18n` |
| Langues absentes | Le document ne prévoit ni i18n, ni URLs par langue | `i18next` + préfixe `/:lang`, sélecteur de langue, `hreflang`, onglets FR/EN dans les formulaires admin |
| Doublon `features/` et `admin/modules/` | Un même domaine à deux endroits, types et services dupliqués | Dans chaque application, un dossier par domaine ; types et client API partagés par package |
| Types à la main | Risque de décalage avec l'API | Types générés depuis la documentation OpenAPI de Laravel (Scramble + `openapi-typescript`) |
| `store/` et `contexts/` vides | État global inutile au départ | TanStack Query pour l'état serveur (admin), loaders de React Router (public) ; pas de store global tant que le besoin n'existe pas |
| Variables CSS | `--color-text-primary` (clair) est défini dans `:root`, donc faux en thème clair | Trois niveaux : primitives, variables sémantiques par thème, variables de composant (voir 4.5) |
| Contraste | Le bleu `#5B8CFF` sur fond clair donne environ 3:1, insuffisant pour du texte (AA exige 4,5:1) ; le turquoise est pire | Variante plus foncée du bleu pour le texte en thème clair ; vérifier chaque couple de couleurs |
| Thème et rendu serveur | Un choix stocké seulement dans le navigateur provoque un flash de mauvais thème | Préférence en cookie lue côté serveur + `color-scheme` ; sombre par défaut conservé |
| Breakpoints | Plages (0 à 639, 640 à 767…) peu adaptées au mobile first | Seuils `min-width` : 640, 768, 1024, 1280 px |
| Composants accessibles | Modal, menu déroulant, select, onglets, infobulle sont difficiles à rendre accessibles à la main | Primitives **Radix UI** habillées avec CSS Modules |
| Polices | Chargement depuis un service externe | Auto-hébergement avec Fontsource (performance, vie privée) |
| Client HTTP | Un seul outil pour deux besoins différents | `fetch` natif côté public (rendu serveur) ; Axios côté admin (gestion automatique du cookie CSRF) |
| Pages manquantes | L'admin n'a pas témoignages, newsletter, redirections, pages et sections, menus, journal d'activité, aperçu de brouillon ; le public n'a pas mentions légales, confidentialité, confirmation et désinscription de newsletter | À ajouter aux listes de pages |
| Qualité | Pas d'outillage de tests ni de contrôles automatiques | ESLint, Prettier, Stylelint, Vitest, Testing Library, Playwright + axe (accessibilité), Husky, CI |

### 4.3 Arborescence retenue

```text
wariss-dev-frontend/
├── apps/
│   ├── web/                          # site public, rendu serveur
│   │   ├── app/
│   │   │   ├── root.tsx
│   │   │   ├── routes.ts
│   │   │   ├── routes/               # une route par page, sous /:lang
│   │   │   ├── features/<domaine>/{components,loaders,types}
│   │   │   ├── components/           # Navbar, Footer, LanguageSwitcher, SectionRenderer, Seo
│   │   │   ├── lib/                  # i18n, seo, api serveur, thème
│   │   │   └── styles/
│   │   └── public/                   # favicon, manifest, images statiques
│   └── admin/                        # SPA privée (noindex)
│       └── src/
│           ├── app/{router,providers,guards}
│           ├── layouts/{AdminLayout,AuthLayout}
│           ├── features/<domaine>/{api,hooks,schemas,components,pages}
│           ├── shared/               # DataTable, TranslationTabs, MediaPicker,
│           │                         # RichTextEditor, SortableList, ConfirmDialog
│           ├── lib/{http,auth,ability}
│           └── styles/
├── packages/
│   ├── ui/                           # design system : composants, tokens, thèmes
│   │   └── src/{components/{Button,Input,Modal,…},styles/{tokens,themes,reset,typography,utilities}.css}
│   ├── api-client/                   # types générés depuis OpenAPI
│   ├── i18n/                         # locales/{fr,en}/*.json
│   └── config/                       # tsconfig, ESLint, Stylelint partagés
├── pnpm-workspace.yaml
├── package.json
└── .github/workflows/                # CI : lint, types, tests, build
```

Le site public sert `/sitemap.xml` et `/robots.txt` en les récupérant auprès de l'API, qui les génère depuis la base.

### 4.4 Organisation d'un domaine

Exemple pour le portfolio, de chaque côté :

```text
apps/web/app/features/portfolio/
├── components/{ProjectCard,ProjectGrid,ProjectFilter}.tsx
├── loaders/projects.ts            # appels API côté serveur
└── types.ts                       # alias des types générés

apps/admin/src/features/portfolio/
├── api/projects.api.ts
├── hooks/useProjects.ts           # TanStack Query
├── schemas/project.schema.ts      # Zod
├── components/{ProjectForm,ProjectTable}.tsx
└── pages/{ProjectsListPage,ProjectCreatePage,ProjectEditPage}.tsx
```

### 4.5 Design system : variables à trois niveaux

```css
/* tokens.css : primitives (valeurs brutes, jamais utilisées dans les composants) */
:root {
  --blue-500: #5B8CFF;
  --blue-700: #2F5FE0;   /* à calibrer : au moins 4,5:1 sur fond clair */
  --teal-500: #35D6C5;
  --midnight: #0B1020;
  --deep-slate: #111827;
  --cloud: #F5F7FB;
  --focus-ring: 0 0 0 3px rgb(91 140 255 / 0.45);
  --z-header: 100;
  --z-modal: 300;
  --z-toast: 400;
}

/* themes.css : variables sémantiques, seules utilisées par les composants */
[data-theme='dark'] {
  --color-bg: var(--midnight);
  --color-surface: var(--deep-slate);
  --color-text: #F5F7FB;
  --color-text-muted: #94A3B8;
  --color-border: #263247;
  --color-accent: var(--blue-500);
  --color-accent-text: var(--blue-500);
}
[data-theme='light'] {
  --color-bg: var(--cloud);
  --color-surface: #FFFFFF;
  --color-text: #111827;
  --color-text-muted: #64748B;
  --color-border: #E2E8F0;
  --color-accent: var(--blue-500);
  --color-accent-text: var(--blue-700);
}
```

Les espacements, rayons, ombres, tailles de police et transitions de ton fichier `variables.css` restent valables et sont déplacés dans `tokens.css`. L'attribut `data-theme` est placé sur la racine de chaque application (sombre pour le site, clair pour l'admin).

### 4.6 Langues et URLs

- `/` redirige vers `/fr` ou `/en` selon le cookie de langue, puis l'en-tête `Accept-Language`.
- Routes publiques : `/:lang`, `/:lang/about`, `/:lang/services/:slug`, `/:lang/portfolio/:slug`, `/:lang/blog/:slug`, etc. Les slugs de contenu sont traduits.
- Chaque page contient `<link rel="alternate" hreflang>` pour les deux langues, `x-default`, une URL canonique et des balises Open Graph dans la langue de la page.
- Textes d'interface : `packages/i18n/locales/{fr,en}`, un fichier par espace (commun, accueil, contact, admin…).
- Dans l'admin, chaque formulaire traduisible propose des onglets FR / EN avec un indicateur des champs manquants.

## 5. Dépendances

Versions : toujours la dernière stable compatible au moment de l'installation. Mes connaissances s'arrêtent à juin 2026 ; vérifie la compatibilité de chaque paquet avec ta version de Laravel ou de React avant de l'installer.

### 5.1 Backend (Composer)

```bash
php artisan install:api          # installe Sanctum
composer require laravel/horizon \
  astrotomic/laravel-translatable \
  spatie/laravel-query-builder \
  dedoc/scramble \
  intervention/image-laravel \
  league/flysystem-aws-s3-v3 \
  spatie/laravel-sitemap \
  spatie/laravel-backup \
  spatie/laravel-honeypot \
  matomo/device-detector \
  league/commonmark
composer require --dev pestphp/pest pestphp/pest-plugin-laravel \
  larastan/larastan laravel/pint barryvdh/laravel-ide-helper laravel/telescope
```

- **Sanctum** : authentification SPA par cookie. **Horizon** : files d'attente sur Redis (extension `phpredis` ou `predis/predis`).
- **Translatable** : tables `<entité>_translations` avec colonne `locale`, exactement la convention du modèle. Si le paquet n'est pas compatible avec ta version de Laravel, un trait maison de quelques dizaines de lignes le remplace.
- **Query Builder** : filtres, tris et relations demandés par l'URL. **Scramble** : documentation OpenAPI générée automatiquement, source des types TypeScript.
- **Intervention Image** : variantes d'images. **Flysystem S3** : stockage objet en production.
- **Sitemap** : sitemap avec alternatives de langue. **Backup** : sauvegardes. **Honeypot** : protection des formulaires.
- **Device Detector** : type d'appareil et détection des robots pour les analytics, sans cookie. **CommonMark** : temps de lecture et extraits des articles.
- **Pest, Larastan, Pint** : tests, analyse statique, style de code. **Telescope** : débogage en développement uniquement.

### 5.2 Frontend (pnpm)

```bash
# site public
pnpm --filter web add react react-dom react-router @react-router/node @react-router/serve \
  i18next react-i18next lucide-react motion \
  react-markdown remark-gfm rehype-sanitize shiki \
  react-hook-form zod @hookform/resolvers date-fns \
  @fontsource-variable/inter @fontsource-variable/space-grotesk @fontsource-variable/jetbrains-mono
pnpm --filter web add -D @react-router/dev vite typescript @types/react @types/react-dom

# administration
pnpm --filter admin add react react-dom react-router @tanstack/react-query @tanstack/react-table axios \
  i18next react-i18next lucide-react motion \
  react-hook-form zod @hookform/resolvers date-fns recharts \
  @tiptap/react @tiptap/pm @tiptap/starter-kit \
  @dnd-kit/core @dnd-kit/sortable @dnd-kit/utilities

# design system
pnpm --filter ui add radix-ui react lucide-react

# outillage, à la racine
pnpm add -D -w eslint typescript-eslint eslint-plugin-react-hooks eslint-plugin-jsx-a11y \
  prettier stylelint stylelint-config-standard \
  vitest @testing-library/react @testing-library/user-event jsdom msw \
  @playwright/test @axe-core/playwright husky lint-staged openapi-typescript
```

- **Rendu serveur** : `react-router` avec `@react-router/*`. **Markdown** : rendu des articles avec nettoyage (`rehype-sanitize`) et coloration du code (`shiki`) côté serveur.
- **Formulaires** : React Hook Form + Zod. **Animations** : `motion` (nouveau nom de Framer Motion), à limiter à des effets discrets et à désactiver avec `prefers-reduced-motion`.
- **Admin seulement** : TanStack Query et Table, Axios, Recharts, Tiptap (éditeur, avec son extension Markdown à valider par un essai, car le contenu des articles est stocké en Markdown), dnd-kit pour réordonner.
- **Radix UI** : modals, menus, onglets, infobulles accessibles. Les icônes restent Lucide React.
- Les polices Fontsource remplacent les liens Google Fonts ; le terminal interactif est codé sans bibliothèque externe, comme simulation côté navigateur.

### 5.3 Prérequis système

PHP (version supportée la plus récente compatible avec Laravel), Composer, Node.js LTS, pnpm, PostgreSQL, Redis, Git. Un serveur Node est nécessaire en production pour le rendu serveur du site public.

## 6. Ordre de mise en place

1. Créer le projet Laravel, les migrations et les seeders (compte `admin`, compte `viewer`, pages système, menus, réglages).
2. API : authentification, rôles, langue, Resources et documentation OpenAPI ; puis les domaines dans l'ordre catalogues, contenus, communication, SEO et analytics.
3. Frontend : monorepo, puis `packages/ui` (tokens, thèmes, composants de base).
4. Administration : connexion, mise en page, catalogues, médias, puis contenus.
5. Site public : mise en page, langues, pages fixes, puis contenus dynamiques et SEO.
6. Analytics, newsletter, sauvegardes, intégration continue.
7. Tests finaux : accessibilité, performance (Lighthouse), sécurité.

## 7. Points à confirmer par un essai

- **Rendu serveur** : construire d'abord une page publique de bout en bout (liste de projets, détail, balises SEO, changement de langue) avant d'engager tout le site. Si l'hébergement d'un serveur Node pose problème, Next.js est l'alternative, au prix d'un routage différent de celui de ton document.
- **Éditeur Markdown** de l'admin : Tiptap avec extension Markdown, ou éditeur Markdown dédié.
- **Compatibilité** de `astrotomic/laravel-translatable` avec la version de Laravel installée.
