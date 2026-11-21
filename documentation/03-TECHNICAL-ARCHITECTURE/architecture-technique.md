# WARISS.DEV — Architecture technique : backend, frontend et dépendances

Nov 21, 2026 · @Wariss BADA

Ce document fixe l'architecture de l'API Laravel et des deux interfaces React, revoit ton document frontend avec des améliorations argumentées, et liste les dépendances à installer. Il s'appuie sur la structure de base de données déjà rédigée.

## 1. Décisions validées

- **Une catégorie par projet et par article** : colonnes `project_category_id` et `post_category_id` (la table `posts` correspond à tes « articles »), aucune table pivot.
- **Conservation** : messages 24 mois, journal d'activité 12 mois, analytics bruts 90 jours ; statistiques agrégées conservées sans donnée identifiante. Des tâches planifiées Laravel purgent les données expirées.
- **Pas de commentaires publics** : ni table, ni modération. Les retours passent par le formulaire de contact.
- **Français et anglais** : URLs `/fr/...` et `/en/...`, tables de traduction, SEO dans les deux langues, onglets FR/EN dans l'administration.

## 2. Vue d'ensemble

Trois applications dans **un seul dépôt Git** (`WARISS_DEV`, décision D-12) : `backend/` pour l'API Laravel, `frontend/` pour le monorepo pnpm, `documentation/` pour la conception.

```text
WARISS_DEV/
├── backend/          # API Laravel
├── frontend/         # monorepo pnpm : apps/web, apps/admin, packages/*
├── documentation/
└── README.md
```

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
backend/
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
- Évolution (D-04) : comme toutes les règles d'accès sont dans les Policies, passer à des rôles et permissions en base (`spatie/laravel-permission`) ne modifiera que les Policies et le middleware, pas les contrôleurs.

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

### 3.8 Aperçu des brouillons (D-09)

Objectif : voir un contenu non publié avec le vrai rendu du site public, sans jamais l'exposer.

1. Dans l'admin, le bouton « Aperçu » appelle `POST /api/v1/admin/preview` avec `{ type, id, locale }` (types autorisés : pages, services, projets, études de cas, expérimentations, articles).
2. L'API vérifie la Policy `view`, puis renvoie une URL signée par Laravel (`URL::temporarySignedRoute`) valable **30 minutes**, qui encode le type, l'identifiant et la langue.
3. L'admin ouvre `https://wariss.dev/:lang/preview/:token` dans un nouvel onglet.
4. Le loader serveur du site public appelle `GET /api/v1/public/preview/{token}`. L'API contrôle la signature et l'expiration, puis renvoie la ressource publique du contenu **quel que soit son statut**.
5. Réponses : `Cache-Control: no-store`, `X-Robots-Tag: noindex, nofollow`, aucune mise en cache Redis, aucun suivi analytics, bandeau « Aperçu — non publié ».

Aucune table n'est nécessaire : le lien est autonome et expire tout seul. Chaque génération d'aperçu est inscrite au journal d'activité. Le `viewer` peut générer un aperçu, car c'est une lecture.

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
frontend/
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
- Routes publiques (D-03) : segments en anglais identiques dans les deux langues, slugs de contenu traduits. `/:lang`, `/:lang/about`, `/:lang/skills`, `/:lang/experience`, `/:lang/projects/:slug`, `/:lang/case-studies/:slug`, `/:lang/services/:slug`, `/:lang/lab/:slug`, `/:lang/blog/:slug`, `/:lang/blog/category/:slug`, `/:lang/blog/tag/:slug`, `/:lang/achievements`, `/:lang/testimonials`, `/:lang/resume`, `/:lang/contact`, `/:lang/terminal`, `/:lang/now`, `/:lang/uses`, `/:lang/newsletter/{confirm,unsubscribe}`, `/:lang/legal-notice`, `/:lang/privacy`, `/:lang/preview/:token`. Liste de référence : cahier des charges v1.1, section 21.2 bis. Les `route_key` de `navigation_items` reprennent ces noms (`projects`, `blog`, `contact`…).
- Chaque page contient `<link rel="alternate" hreflang>` pour les deux langues, `x-default`, une URL canonique et des balises Open Graph dans la langue de la page.
- Textes d'interface : `packages/i18n/locales/{fr,en}`, un fichier par espace (commun, accueil, contact, admin…).
- Dans l'admin, chaque formulaire traduisible propose des onglets FR / EN avec un indicateur des champs manquants.

### 4.7 Types partagés entre l'API et le frontend (D-10)

Le backend et le frontend sont dans le même dépôt (D-12) : le contrat entre eux est le fichier OpenAPI.

1. **API** : Scramble génère la documentation. La commande d'export de Scramble écrit `backend/openapi.json`, qui est **versionné**. La CI régénère le fichier et échoue s'il diffère de la version committée : tout changement de contrat est donc visible dans la revue de code.
2. **Frontend** : le script `pnpm api:types` lit `../backend/openapi.json` (surchargeable par `OPENAPI_SOURCE`), lance `openapi-typescript` et écrit `frontend/packages/api-client/src/schema.d.ts`, qui est **versionné**.
3. La CI régénère les types et échoue s'il y a une différence. Le build TypeScript détecte ensuite toute utilisation devenue incompatible. Un changement d'API et l'adaptation du frontend peuvent ainsi arriver dans le **même commit**.
4. `packages/api-client` exporte des alias lisibles (`Project`, `Post`…) et un client typé fin. Aucun type d'API n'est écrit à la main.

## 5. Dépendances

Versions : toujours la dernière stable compatible au moment de l'installation. Mes connaissances s'arrêtent à juin 2026 ; vérifie la compatibilité de chaque paquet avec ta version de Laravel ou de React avant de l'installer.

### 5.1 Backend (Composer)

```bash
php artisan install:api          # installe Sanctum
composer require laravel/horizon predis/predis \
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

**Installé le 21/11/2026 (Laravel 13.43, PHP 8.4)** — versions retenues par Composer :

| Paquet | Version | Remarque |
| --- | --- | --- |
| laravel/sanctum | via `install:api` | |
| predis/predis | ^3.7 | |
| astrotomic/laravel-translatable | ^11.17 | compatible Laravel 13 : le trait maison n'est pas nécessaire |
| spatie/laravel-query-builder | ^7.3 | |
| dedoc/scramble | ^0.13.55 | |
| intervention/image-laravel | ^4.1 | pilote GD (D-11) |
| league/flysystem-aws-s3-v3 | ^3.35 | |
| spatie/laravel-sitemap | ^8.2 | |
| spatie/laravel-backup | ^10.5 | |
| spatie/laravel-honeypot | ^4.7 | |
| matomo/device-detector | ^6.5 | |
| league/commonmark | ^2.10 | |
| pestphp/pest (+ plugin Laravel) | ^4.7 / ^4.1 | Pest 5 exigerait PHPUnit 13 ; Laravel 13 est livré avec PHPUnit 12 |
| larastan/larastan | ^3.15 | niveau 6, `phpstan.neon` |
| barryvdh/laravel-ide-helper | ^3.8 | |
| laravel/telescope | ^5.29 | enregistré seulement en environnement `local` |
| laravel/boost | ^2.10 | consignes et serveur MCP Laravel pour l'assistant IA (`CLAUDE.md`, `.mcp.json`), dev uniquement |

**Horizon n'est pas encore installé** : il exige `pcntl` et `posix`, absents sous Windows, et ne sert qu'avec Redis en production. Il sera ajouté à l'étape du déploiement (D-11).

Commandes du backend : `composer dev` (serveur + file d'attente), `composer test`, `composer lint` / `composer format` (Pint), `composer analyse` (Larastan), `composer check` (les trois).

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

### 5.4 Poste de développement (Windows, vérifié le 21/11/2026) — D-11

| Outil | Version constatée | Statut |
| --- | --- | --- |
| PHP (Laragon) | 8.4.22 NTS | OK. `pdo_pgsql` et `pgsql` activées le 21/11/2026 (sauvegarde : `php.ini.bak-2026-11-21`) |
| Composer | 2.10.1 | OK |
| PostgreSQL | 18.3, service `postgresql-x64-18`, port 5432 | OK. `psql` absent du PATH : `C:\Program Files\PostgreSQL\18\bin` |
| Node.js | 26.13.1 (LTS), dans `C:\Users\wariss.bada\node-v26.13.1-win-x64\` | OK (remplace la 25.9.0 le 21/11/2026) |
| pnpm | 12.4.2 | OK |
| Redis | Laragon 5.0.14 (portage Windows ancien), extension PHP `redis` absente | Non utilisé en local ; client `predis/predis` pour la production |
| Docker | 29.7.2 | Installé mais **inaccessible** (droits du poste d'entreprise) : aucun outil du projet ne doit en dépendre en local |
| Git | 2.54.0 | OK |

Conséquences en développement sous Windows :

- **Horizon ne fonctionne pas sous Windows** : il exige l'extension `pcntl`, qui n'existe pas sur cette plateforme. En local, les files tournent avec `php artisan queue:listen` (lancé par `composer dev`) ; Horizon sera installé à l'étape du déploiement, pour la production Linux seulement.
- **Redis** : client `predis/predis` (pur PHP, sans extension). En local, **cache, files et sessions utilisent le pilote `database`** (PostgreSQL), car Docker n'est pas accessible ; la production utilise Redis. Le code ne doit donc jamais dépendre d'une fonction propre à Redis : seulement des façades `Cache`, `Queue` et `Session` de Laravel. Les tests tournent avec les pilotes `array` et `sync`.
- **Images** : `imagick` absente, Intervention Image utilise le pilote **GD** (présent).
- **Base de données** : en local, l'application se connecte avec le compte `postgres` (super-utilisateur), car le poste ne permet pas de créer un rôle dédié. **En production, un rôle dédié non super-utilisateur, propriétaire de la base, est obligatoire.** Le code ne doit donc jamais dépendre de droits super-utilisateur : l'extension `unaccent` est « trusted » et peut être créée par le propriétaire de la base.
- **PowerShell** est bloqué par une stratégie de groupe sur ce poste : les scripts du projet sont écrits pour Bash (Git Bash) ou en PHP/Node, jamais en `.ps1`.

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

## 8. Registre des décisions

Décisions prises le 21 novembre 2026 pour lever les incohérences entre documents. Toute nouvelle décision structurante est ajoutée ici avec le numéro suivant.

| N° | Sujet | Décision | Raison | Documents mis à jour |
| --- | --- | --- | --- | --- |
| D-01 | Deux cahiers des charges | La v1 devient la v1.1, seule référence ; les éléments propres au premier document y sont fusionnés (Now, Uses, thème, terminal, contact terminal, phases). L'ancien est marqué « remplacé » et sera archivé. | Une seule source de vérité, sans perdre d'exigence | Cahier des charges v1.1, ancien cahier des charges |
| D-02 | Périmètre V1 contradictoire | Périmètre V1 de la v1.0 conservé et livré en 4 lots (fondation, contenus, interactif, suivi). Toute la base et le FR/EN dès le lot 1. Campagnes de newsletter après la V1. | Le document le plus récent fait foi pour le périmètre ; les phases donnent l'ordre de livraison | Cahier des charges §28.1 bis |
| D-03 | `/projects` ou `/portfolio` | `/:lang/projects/:slug` ; segments de route en anglais dans les deux langues, slugs traduits. | Cohérent avec les cahiers des charges et les `route_key` ; des segments fixes simplifient le routage, le sitemap et `hreflang` | Cahier des charges §21.2 bis, architecture §4.6 |
| D-04 | Tables de rôles ou colonne | Colonne `users.role` (admin, viewer) en V1 ; évolution possible via les Policies. | Deux comptes prévus : des tables de permissions seraient de la complexité inutile | Cahier des charges §19.3, §19.7, §30 ; base de données ; architecture §3.4 |
| D-05 | Arborescence de la documentation | Arborescence unique décrite dans le `README.md` racine (00-VISION à 07-DEPLOYMENT, 99-ARCHIVE). | Deux propositions concurrentes | `README.md` (ex-`documentation.md`), cahier des charges §33 |
| D-06 | Slugs et suppression douce | Slug réservé tant que le contenu existe, même supprimé en douceur ; redirection 301 automatique si un slug publié change. | Restauration sans conflit, pas d'URL indexée réattribuée, SEO préservé | Base de données (conventions) |
| D-07 | E-mail insensible à la casse | Minuscules à l'enregistrement + index unique `lower(email)`. Pas de `citext`. | Simple, sans extension, fiable avec Eloquent | Base de données (D1, D13, index) |
| D-08 | Recherche FR/EN | `search_vector` générée avec la configuration `french` ou `english` selon `locale`, pondérée, sans accents via `immutable_unaccent()`. | La recherche française est fausse avec la configuration anglaise, et inversement | Base de données (conventions, index, migrations) |
| D-09 | Aperçu des brouillons | Lien signé Laravel de 30 minutes, route `/:lang/preview/:token`, `no-store` + `noindex`, aucune table. | Exigence du cahier des charges §22.3 sans exposition publique | Architecture §3.8, cahier des charges §22.3 |
| D-10 | Types TypeScript entre l'API et le frontend | `backend/openapi.json` versionné, types générés et versionnés dans `frontend/packages/api-client`, contrôle de différence en CI. | Le contrat d'API devient explicite et vérifié automatiquement | Architecture §4.7 |
| D-12 | Un ou plusieurs dépôts | Un seul dépôt `WARISS_DEV` : `backend/`, `frontend/`, `documentation/`. | Demande du propriétaire ; un seul historique, contrat d'API et frontend modifiables dans le même commit, une seule CI | Architecture §2, §3.2, §4.3, §4.7 ; README |
| D-13 | Précisions du modèle Médias | Disque `public` ajouté (local = privé, public = servi par URL, s3) ; média = fichier complet **ou** URL externe (CHECK `media_source_check`) ; visibilité privée par défaut ; dimensions renseignées ensemble. Tables de traduction : `locale` en `varchar(5)`, unique `(<entité>_id, locale)`, langues `fr` puis `en` et repli sur `fr` (`config/translatable.php`, source unique des langues) | Les disques Laravel distinguent privé et public ; une vidéo externe n'a pas de fichier ; « privé par défaut » évite d'exposer un document par erreur | Base de données (D14) |
| D-14 | Conventions des migrations | Pivots : clé primaire composée, CASCADE, index inverse, sans `id` ni dates. Contraintes de traduction nommées explicitement (limite de 63 caractères de PostgreSQL). Ordre d'affichage par le trait `OrderedByPosition` (`position`, puis `id`). Seeders : catégories de départ uniquement, jamais de technologies ni de compétences inventées | Pivots légers (§29.5) ; noms tronqués illisibles dans les erreurs ; honnêteté des contenus (§1.2 du cahier des charges) | Base de données (conventions) |
| D-15 | Sécurité des URL, menus et réglages | **URL** : toute colonne d'URL n'accepte que `https://`, `http://` (et `mailto:` pour les liens de contact, chemins internes `/…` et ancres `#…` pour les boutons) : `javascript:`, `data:` et `//domaine` sont refusés par CHECK. **Menus** : les éléments pointent vers les pages système par `type = page` ; `route` est réservé aux routes du frontend sans page en base. **Réglages** : la valeur JSON doit correspondre au type déclaré ; un réglage traduisible est de type texte et n'a pas de valeur propre ; le modèle `SiteSetting` gère ses traductions lui-même (colonne et traduction portent le même nom `value`). **Pages système** : créées en brouillon, publiées depuis l'administration | Bloquer les XSS par lien dès la base ; une seule façon de lier une page (le statut de publication de la page est respecté) ; intégrité des réglages ; pages légales à rédiger avant publication | Base de données (D1, D2, D18) |
| D-16 | Visiteurs anonymes et mesure d'audience | Un seul auteur (le propriétaire) et deux comptes d'administration ; aucun compte visiteur, aucune connexion côté public. Audience mesurée sans cookie ni donnée personnelle : vues de pages (hash quotidien non réversible), provenance, appareil, pays, événements dont `share` (bouton de partage, réseau) ; liens de partage balisés `utm_medium=share` | Demande du propriétaire : savoir qui visite et partage, sans inscription des visiteurs ; respect de la vie privée (§20.4) sans bandeau de cookies pour la mesure | Base de données (D17) |
| D-17 | Version courante d'un CV | Clé étrangère composée `resumes (id, current_version_id)` → `resume_versions (resume_id, id)` avec `ON DELETE SET NULL (current_version_id)` ; CHECK : CV public = actif avec une version, CV par défaut = public | Avec une clé simple, un CV pouvait désigner la version d'un autre CV et rendre public un fichier privé ; la version courante d'un CV public ne peut plus être supprimée par erreur | Base de données (D15) |
| D-11 | Développement sous Windows | `queue:listen` en local ; Horizon installé au déploiement, production Linux seulement ; pas de Docker en local (inaccessible) ; tests sur PostgreSQL `wariss_dev_test`, jamais SQLite ; client `predis/predis` ; pilote GD pour les images ; scripts en Bash, PHP ou Node ; Node LTS pair. | `pcntl`, `phpredis` et `imagick` sont indisponibles sur le poste ; PowerShell est bloqué par stratégie de groupe | Architecture §5.4 |
