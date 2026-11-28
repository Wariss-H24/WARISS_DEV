# WARISS.DEV : fichier de reprise pour Claude

> Mis à jour le 28/11/2026. Ce fichier sert à reprendre le travail, sur ce poste ou sur un autre, sans perdre le contexte.
> **Claude : lis ce fichier en entier avant d'écrire du code.** Lis ensuite les documents de référence (section 2), puis `backend/CLAUDE.md` et `backend/.ai/rules` s'il existe.

---

## 1. Projet et consignes de l'utilisateur

WARISS.DEV est une plateforme professionnelle personnelle : portfolio, services, blog, laboratoire, terminal, CV et contact. Un CMS privé l'administre et elle couvre 18 domaines fonctionnels.

**Consignes permanentes (à respecter sans exception) :**

- **Réponds toujours en français.**
- L'utilisateur veut un travail professionnel et complet, et laisse Claude prendre les décisions. Les nouvelles décisions continuent la numérotation D-xx : la dernière est **D-19**, la prochaine est **D-20**. Note-les dans le registre (architecture, section 8).
- **Tout reste dans le dossier `WARISS_DEV`** : `backend/`, `frontend/` et `documentation/`. Aucun dossier à côté.
- **Le dépôt est PUBLIC** : https://github.com/Wariss-H24/WARISS_DEV.
  - Aucune donnée personnelle ni aucun secret dans les fichiers versionnés. Les e-mails des comptes et le mot de passe de la base sont uniquement dans `backend/.env`, qui n'est pas versionné.
  - Les tests utilisent de fausses adresses en `.test`.
- Les mots de passe se définissent avec `php artisan app:set-password {email}` (saisie masquée), jamais dans un fichier.
- E-mail Git du dépôt : `219896005+Wariss-H24@users.noreply.github.com`. Configure-le en local sur un nouveau poste : `git config user.email ...`.
  - Les 2 premiers commits gardent l'ancien e-mail, par choix de l'utilisateur. **Ne réécris jamais l'historique.**
- Le dossier `.claude/` à la racine n'est **jamais** commité.
- **Après chaque push, vérifie la CI toi-même.** Ne demande pas à l'utilisateur d'ouvrir GitHub Actions.
  - Utilise le badge : `https://github.com/Wariss-H24/WARISS_DEV/actions/workflows/backend.yml/badge.svg?branch=main`.
  - Ou l'API `https://api.github.com/repos/Wariss-H24/WARISS_DEV/actions/runs?per_page=1` : elle est limitée à 60 requêtes par heure sans authentification, donc au plus une interrogation par minute.
- **Avant chaque commit**, lance Pint, Larastan, toute la suite Pest et `php artisan scramble:export`, si une route ou une réponse a changé. Tout doit être propre. Messages de commit en français, style `feat(api): ...`.
- Base locale :
  - compte PostgreSQL `postgres` (superutilisateur) ;
  - **ne lance jamais `REASSIGN OWNED BY postgres`** : le serveur héberge d'autres projets ;
  - en production, il faudra un rôle dédié (architecture, section 5.4).
- Un seul auteur, et seulement **2 comptes** : `admin`, et `viewer` en lecture seule. Les visiteurs n'ont **jamais** de compte (D-16).
- Règle de méthode : on conçoit avant de coder. Relis la documentation concernée. Si deux documents se contredisent, signale-le et décide (D-xx) au lieu de choisir en silence.

## 2. Documents de référence (dans `documentation/`)

| Fichier | Contenu |
|---|---|
| `00-VISION/cahier-des-charges-general.md` | Cahier des charges v1.1, **seule référence** fonctionnelle (routes publiques : §21.2 bis) |
| `02-DATABASE/structure-base-de-donnees.md` | Les 86 tables |
| `03-TECHNICAL-ARCHITECTURE/architecture-technique.md` | Architecture ; **section 8 = registre des décisions D-01 à D-19** |
| `03-TECHNICAL-ARCHITECTURE/comptes-et-mots-de-passe.md` | Notice des comptes `admin` et `viewer` |
| `04-API-SPECIFICATIONS/conventions-api.md` | **Conventions de l'API**, à lire avant le frontend : accès, langue, formats, public, formulaires, audience, aperçu, médias |
| `backend/openapi.json` | Contrat OpenAPI généré par Scramble (128 chemins), versionné et vérifié par la CI |
| `99-ARCHIVE/` | Ancien cahier des charges, périmé |

## 3. Stack et environnement

- **Backend** (`backend/`) : Laravel 13, PHP 8.4, PostgreSQL 18, Pest 4, Larastan niveau 6, Pint, Telescope (local seulement), Laravel Boost.
- **Paquets utilisés** :
  - astrotomic/translatable ;
  - spatie/laravel-query-builder 7, laravel-sitemap ;
  - Sanctum (SPA) ;
  - intervention/image v4 (GD) ;
  - matomo/device-detector ;
  - dedoc/scramble.
- **Frontend** (`frontend/`, **pas encore commencé**) : monorepo pnpm avec `apps/web` (React Router v7 en SSR), `apps/admin` (SPA) et `packages/{ui,api-client,i18n,config}`.
- **Poste de développement Windows d'entreprise** :
  - PowerShell est bloqué : utiliser Git Bash ;
  - PHP vient de Laragon ; pas d'imagick, de pcntl ni de phpredis ;
  - pas de Docker : cache, file d'attente et sessions utilisent le driver `database` ;
  - **le shell bash altère les antislashs** (namespaces PHP) dans `sed`, les heredocs et `php -r` : écrire et modifier les fichiers PHP avec les outils Write et Edit.
- Bases : `wariss_dev` et `wariss_dev_test`. Sur un nouveau poste :
  1. remplis `.env` à partir de `.env.example` ;
  2. lance `composer install` ;
  3. lance `php artisan migrate --seed`.
- Variables propres au projet, dans `.env.example` :
  - `SITE_URL` et `ADMIN_URL` ;
  - `TURNSTILE_SITE_KEY` et `TURNSTILE_SECRET_KEY` (vides en local : vérification désactivée, refusée en production) ;
  - `FRONTEND_URLS` et `SANCTUM_STATEFUL_DOMAINS`.
- **CI** (`.github/workflows/backend.yml`) : Pint, Larastan, Pest sur PostgreSQL 18, puis vérification que `openapi.json` est à jour.
- **Tests** : `tests/Pest.php` envoie `Accept-Language: fr` (le client de test de Symfony envoie `en-us` par défaut). Un test en anglais passe `?locale=en`.

## 4. Ce qui est FAIT : backend V1 terminé (571 tests, CI verte)

Commits principaux, du plus ancien au plus récent : `1f58fa4` (CRUD), `5b9da00` (médias), `9030e42` (CV, profil, réglages), `d0b73ec` (données sensibles), `c3348d1` (SEO, statistiques, tableau de bord, tâches), `aca4449` (API publique), `804b6d2` (formulaires et français), `0e69b0d` (OpenAPI).

### 4.1 Base de données (86 tables)
Elle couvre les domaines D1 à D18 avec de nombreuses contraintes CHECK. Les données périmées sont purgées par `model:prune` (03:00) : messages après 24 mois, journal après 12 mois, statistiques brutes après 90 jours.

Conventions :
- `id()->generatedAs()`, `timestampsTz()` ;
- contraintes CHECK écrites avec `DB::statement` ;
- modèles dans `app/Models/<Domaine>/` ;
- **morph map stricte** dans `AppServiceProvider` ;
- `composer ide-helper` après chaque nouveau modèle.

### 4.2 Authentification et rôles
- Connexion, déconnexion et `me` (Sanctum SPA), bloquées après 5 échecs.
- `EnsureCanWrite` met le `viewer` en lecture seule.
- La gate `view-sensitive-data` est réservée à `admin`.
- Filet de sécurité dans `bootstrap/app.php` : 23514 et 23502 → 422 ; 23505 → 409.

### 4.3 API d'administration (`/api/v1/admin`)
- **Socle CRUD** : `AdminCrudController` avec ses points d'extension :
  - `model`, `formRequest` (facultatif pour un contenu en lecture seule), `resourceClass` ;
  - `relations`, `children`, `beforeSave`, `detailRelations` ;
  - `filters`, `sorts`, `defaultSort`, `baseQuery`.

  S'y ajoutent `SaveModel` (transaction, traductions, redirections 301, journal), `AdminFormRequest` (aides de validation) et `AdminResource`.
- **29 types de contenus** en CRUD, avec leurs règles métier, et la corbeille pour les contenus qui en ont une.
- **Médias** :
  - `MediaController` ;
  - règle `AllowedUpload`, qui contrôle le type MIME réel, la taille et les pixels ; SVG refusé ;
  - `MediaStorage` : ré-encodage sans EXIF, disque public ou privé, déplacement quand la visibilité change ;
  - `MediaUsages` : suppression protégée, avec confirmation, et usages bloquants ;
  - `GET media/{id}/file`.
- **CV** : `POST|PATCH|DELETE resumes/{id}/versions/{versionId}`. Le PDF est privé et numéroté ; la version publiée ne peut pas être supprimée.
- **Profil** : ressource unique (`GET` et `PUT`/`PATCH` sur `profile`).
- **Réglages** : `GET` et `PATCH settings`, par lot et validés selon leur type. Le `viewer` ne voit que les réglages publics.
- **SEO** : `GET` et `PUT seo/{type}/{id}`.
- **Tableau de bord** (`dashboard`), **statistiques** (`analytics?from&to`) et **lien d'aperçu** (`preview-link`).
- **Données sensibles** (admin seulement, même en lecture) :
  - `messages` : lu à l'ouverture, statut, note interne, corbeille ;
  - `newsletter-subscribers` : désinscription, effacement ;
  - `activity-logs` et `accounts`, en lecture seule.

### 4.4 API publique (`/api/v1/public`)
- **`PublicPresenter`** : aucun champ interne, aucun média privé ; un projet confidentiel n'expose ni client ni liens ; langue avec repli et `slugs`.
- **`PublicVisibility`** : décide si un contenu référencé est public.
- Routes de lecture :
  - `site`, `pages/{clé|slug}` ;
  - `projects`, `case-studies`, `services`, `lab`, `posts` (liste et slug) ;
  - `technologies`, `skills`, `taxonomies/{type}` ;
  - `experiences`, `educations`, `certifications`, `achievements`, `timeline`, `testimonials` ;
  - `search` ;
  - `resume` et `resume/download` (compté) ;
  - `redirect`, `sitemap.xml`, `preview/{token}` (jeton HMAC `PreviewToken`, 30 minutes).
- **Formulaires** :
  - `contact` : Turnstile, champ piège `website`, empreinte IP, e-mail à l'admin (`NewContactMessage`) ;
  - `newsletter/subscribe|confirm|unsubscribe` : double opt-in, lien valable 7 jours, désinscription signée.
- **Mesure d'audience** : `track/view` et `track/event` par `AudienceTracker`, avec une empreinte HMAC quotidienne, sans IP ni cookie ; robots, DNT et GPC ignorés.

### 4.5 Tâches planifiées (`routes/console.php`)
- `content:publish-scheduled`, chaque minute ;
- `analytics:aggregate`, à 00:15 pour la veille, avec `{date?} --days=` ;
- `model:prune`, à 03:00.

### 4.6 Langues et documentation
- Messages en français dans `lang/fr/*.php`, noms de champs en anglais dans `lang/en/validation.php`, textes de l'API traduits dans `lang/en.json`.
- Toute nouvelle chaîne `__('…')` en français doit être ajoutée à `lang/en.json`.
- La décision D-19 et les conventions de l'API sont rédigées ; le README est à jour.

## 5. CE QUI RESTE À FAIRE

### Backend (secondaire, à faire si l'utilisateur le demande ou au déploiement)
1. **Envoi des campagnes de newsletter** : prévu après la V1 (D-19). Aujourd'hui, les campagnes se rédigent seulement. Le moment venu, il faudra :
   - une commande planifiée ;
   - une file d'attente ;
   - le lien `NewsletterSubscriber::unsubscribeUrl()` dans chaque e-mail.
2. **Cache Redis des réponses publiques** (architecture §3.7), invalidé à chaque publication. Utile en production seulement.
3. **Mot de passe oublié** dans `auth`, s'il est souhaité. Aujourd'hui, `app:set-password` suffit.
4. **Déploiement** (`documentation/07-DEPLOYMENT/`) : Horizon, rôle PostgreSQL dédié, S3, sauvegardes (spatie/laravel-backup), en-têtes CSP et HSTS.

### Frontend : site public et administration FAITS (28/11/2026)
- **Commencé à la demande de l'utilisateur** (« très bien fait, comme un pro, avec une UI puissante »). Il veut **une phrase avant chaque action** pour savoir ce que je fais.
- Versions et choix : décision **D-20**. Design system : `documentation/05-DESIGN-SYSTEM/design-system.md`.
- **Fait** :
  - le monorepo pnpm (`frontend/`) : `packages/config`, `packages/api-client` (`schema.d.ts` généré par `pnpm api:types`, plus `public.ts`), `packages/i18n` (FR/EN, une instance par requête) et `packages/ui` (tokens, thèmes, Button, Card projecteur, Badge, Field/Input/Select/Textarea/Checkbox, Dialog/Sheet, Tooltip, AuroraBackground…) ;
  - **`apps/web`** (React Router 8 en rendu serveur), 27 routes :
    - accueil (hero + fenêtre de code, bandeau de technologies, bento, sections de l'administration via `SectionRenderer`) ;
    - listes et détails des projets, études de cas, services, laboratoire et blog (catégories et étiquettes) ;
    - à propos, compétences, parcours, réalisations, témoignages, CV, contact (Turnstile et champ piège, envoi depuis le navigateur), terminal ;
    - newsletter (confirmation, désinscription), aperçu signé, pages système et personnalisées, sitemap, robots, redirections ;
  - les fonctions communes : palette ⌘K (navigation, actions, recherche), thème en cookie lu côté serveur, sélecteur de langue avec slugs traduits, SEO complet (canonique, hreflang, Open Graph, JSON-LD), mesure d'audience, partage avec `utm_medium=share` ;
  - les contrôles : ESLint, Stylelint et Prettier propres, TypeScript propre, build OK ; les 20 adresses testées en direct contre l'API (200, 302 et 404 attendus) ;
  - la CI `.github/workflows/frontend.yml`.
- **Lancer en local** :
  1. dans `backend/` : `php artisan serve --port=8000` ;
  2. dans `frontend/apps/web/` : `API_URL=http://127.0.0.1:8000/api/v1 ./node_modules/.bin/react-router dev`, puis ouvrir http://localhost:5173.
  Les pages du seeder sont en brouillon : une page système répond 404 tant qu'elle n'est pas publiée.
- **Pièges du poste** :
  - le `cd` déclenche fnm, qui affiche une erreur sans conséquence : enchaîner avec `;` plutôt que `&&` ;
  - préfixer le `PATH` avec `~/node-v26.13.1-win-x64/node-v26.13.1-win-x64` ;
  - Lucide 1.x n'a plus `Github`, `CheckCircle2`, `CircleAlert`, `Globe2` ni `Code2` : utiliser `SocialIcon`, `CircleCheck`, `CircleExclamationPoint`, `Globe` et `Code` ;
  - un module `*.server.ts` ne doit jamais être importé par une fonction `meta` (l'adresse du site passe par le loader racine, `siteUrlOf`).
- **Administration `apps/admin` : FAITE (28/11/2026, D-21)**
  - SPA Vite + React Router 8, TanStack Query, Axios (Sanctum), React Hook Form ; interface en français, thème clair.
  - **Moteur CRUD déclaratif** : `src/crud/resources.ts` décrit les 30 types (champs, colonnes, filtres), en miroir des FormRequest du backend. Les composants génériques sont :
    - `ResourceListPage` : filtres, recherche, tri, pagination, corbeille, restauration ;
    - `ResourceFormPage` : onglets FR/EN avec compteur d'erreurs, erreurs 422 sur les champs, `Ctrl+S`, garde des modifications non enregistrées, lecture seule pour le viewer ;
    - les champs dans `src/crud/fields/` : texte, slug, Markdown avec aperçu, relation simple et multiple, média, enfants, JSON, couleur.
  - **Pages dédiées** :
    - connexion, tableau de bord, statistiques ;
    - bibliothèque de médias (glisser-déposer, doublons, usages, médias externes) ;
    - profil, réglages par lot.
  - **Panneaux** : SEO par contenu, aperçu signé, versions de CV (PDF), détail d'un message.
  - Pour ajouter un type de contenu, il suffit d'une entrée dans `resources.ts` et d'une entrée dans `layouts/navigation.ts`.
  - **Lancer** : dans `frontend/apps/admin/`, lancer `./node_modules/.bin/vite --port 5174`, puis ouvrir http://localhost:5174. L'API doit tourner sur le port 8000. Le mot de passe se définit avec `php artisan app:set-password {email}`.
- **Reste** :
  - des tests Vitest pour `packages/ui` et l'administration, et Playwright + axe pour le site ;
  - une revue visuelle dans le navigateur avec du vrai contenu ;
  - la suppression définitive depuis la corbeille dans l'interface (l'API la permet déjà : `DELETE {id}/force`) ;
  - le réordonnancement par glisser-déposer (aujourd'hui : champ « Ordre » et flèches dans les éléments enfants).

## 6. Commandes utiles (depuis `backend/`)

```bash
php artisan test --compact                   # toute la suite Pest (571 tests)
php artisan test --compact --filter=Media    # tests ciblés
vendor/bin/pint --dirty                      # style
vendor/bin/phpstan analyse                   # Larastan (si « Undefined constant LARAVEL_VERSION » : vendor/bin/phpstan clear-result-cache)
php artisan scramble:export                  # régénère openapi.json (à committer)
php artisan route:list --path=api/v1/public
php artisan schedule:list
php artisan analytics:aggregate 2026-11-20 --days=7
```

Pièges connus :
- Le JSON de Pest compte séparément `errors` et `failed` : vérifie les deux.
- Une closure de dataset Pest avec un paramètre typé `Closure` n'est pas résolue : n'utilise qu'un seul niveau de `fn () =>`.
- `Collection::mapInto` et les ressources passent l'index en 2ᵉ argument : n'ajoute pas de paramètre au constructeur d'une ressource ; utilise une méthode, comme `withUsages()`.
- `Builder<Model>` est invariant pour Larastan : dans une méthode surchargée, réutilise `parent::baseQuery()`.
- Appelle un scope par attribut sur un `Builder<Model>` générique avec `->scopes(['published'])`.
- Une erreur SQL dans une transaction PostgreSQL bloque toute la suite de la transaction : enveloppe chaque opération risquée dans sa propre `DB::transaction`.
- Pour tester une contrainte CHECK, passe par `DB::table()->update()`.
- `scramble:export` lit le schéma des tables : il lui faut une base migrée (dans la CI, `wariss_dev_test`).
