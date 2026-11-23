# WARISS.DEV : fichier de reprise pour Claude

> Mis à jour le 23/11/2026. Ce fichier sert à reprendre le travail sur un autre poste sans perdre le contexte.
> **Claude : lis ce fichier en entier avant d'écrire du code.** Lis ensuite les documents de référence (section 2), puis `backend/CLAUDE.md` et `backend/.ai/rules` s'il existe.

---

## 1. Projet et consignes de l'utilisateur

WARISS.DEV est une plateforme professionnelle personnelle : portfolio, services, blog, laboratoire, terminal, CV et contact. Un CMS privé l'administre et elle couvre 18 domaines fonctionnels.

**Consignes permanentes (à respecter sans exception) :**

- **Réponds toujours en français.**
- L'utilisateur veut un travail professionnel et complet. Il t'a demandé de **terminer tout le backend en autonomie et de prendre les décisions toi-même**. Les nouvelles décisions continuent la numérotation D-xx, après D-18 ; la prochaine est **D-19**. Note-les dans le registre (architecture, section 8).
- **Tout reste dans le dossier `WARISS_DEV`** : `backend/`, `frontend/` et `documentation/`. Aucun dossier à côté.
- **Le dépôt est PUBLIC** : https://github.com/Wariss-H24/WARISS_DEV.
  - Aucune donnée personnelle ni aucun secret dans les fichiers versionnés. Les e-mails des comptes et le mot de passe de la base sont uniquement dans `backend/.env`, qui n'est pas versionné.
  - Les tests utilisent de fausses adresses en `.test`.
- Les mots de passe se définissent avec `php artisan app:set-password {email}` (saisie masquée), jamais dans un fichier.
- E-mail Git du dépôt : `219896005+Wariss-H24@users.noreply.github.com`. Configure-le en local sur le nouveau poste : `git config user.email ...`.
  - Les 2 premiers commits gardent l'ancien e-mail, par choix de l'utilisateur. **Ne réécris jamais l'historique.**
- Le dossier `.claude/` à la racine n'est **jamais** commité.
- **Après chaque push, vérifie la CI toi-même.** Ne demande pas à l'utilisateur d'ouvrir GitHub Actions.
  - Utilise le badge : `https://github.com/Wariss-H24/WARISS_DEV/actions/workflows/backend.yml/badge.svg?branch=main` et cherche `passing` ou `failing`.
  - L'API GitHub sans authentification est limitée à 60 requêtes par heure : interroge-la au plus une fois par minute.
- **Avant chaque commit**, lance Pint, Larastan et toute la suite Pest. Tout doit être propre. Messages de commit en français, style `feat(api): ...`.
- Base locale :
  - compte PostgreSQL `postgres` (superutilisateur) ;
  - **ne lance jamais `REASSIGN OWNED BY postgres`** : le serveur héberge d'autres projets ;
  - en production, il faudra un rôle dédié (architecture, section 5.4).
- Un seul auteur, et seulement **2 comptes** : `admin`, et `viewer` en lecture seule. Les visiteurs n'ont **jamais** de compte (D-16).
  - La mesure d'audience se fait sans cookie : empreinte quotidienne irréversible, jamais d'IP stockée.
  - Les partages sont mesurés par l'événement `share` et par `utm_medium=share`.
- Règle de méthode : on conçoit avant de coder. Relis la documentation concernée avant de coder. Si deux documents se contredisent, signale-le et décide (D-xx) au lieu de choisir en silence.

## 2. Documents de référence (dans `documentation/`)

| Fichier | Contenu |
|---|---|
| `00-VISION/cahier-des-charges-general.md` | Cahier des charges v1.1, **seule référence** fonctionnelle |
| `02-DATABASE/structure-base-de-donnees.md` | Les 86 tables |
| `03-TECHNICAL-ARCHITECTURE/architecture-technique.md` | Architecture ; **section 8 = registre des décisions D-01 à D-18** |
| `03-TECHNICAL-ARCHITECTURE/comptes-et-mots-de-passe.md` | Notice des comptes `admin` et `viewer` |
| `99-ARCHIVE/` | Ancien cahier des charges, périmé |

Résumé des décisions clés :
- livraison de la V1 en 4 lots ;
- routes de la forme `/:lang/projects/:slug` ;
- rôle stocké dans une colonne (`admin`, `viewer`) ;
- un slug reste réservé tant que le contenu est dans la corbeille ;
- e-mails en minuscules, index unique sur `lower(email)` ;
- recherche plein texte avec une `regconfig` par langue et `immutable_unaccent` ;
- aperçu des brouillons par URL signée de 30 minutes ;
- `openapi.json` et les types TypeScript générés sont commités ;
- D-06 : redirection 301 automatique quand le slug d'un contenu publié change ;
- D-13 : médias privés sur un disque privé ;
- D-15 : chaque colonne URL a une contrainte CHECK sur le schéma (http ou https) ;
- D-16 : audience sans cookie ;
- D-18 : nettoyage des références polymorphes.

## 3. Stack et environnement

- **Backend** (`backend/`) : Laravel 13, PHP 8.4, PostgreSQL 18, Pest 4, Larastan niveau 6, Pint, Telescope (local seulement), Laravel Boost.
- **Paquets** :
  - astrotomic/translatable (FR/EN, tables `*_translations`) ;
  - spatie/laravel-query-builder 7 ;
  - Sanctum en mode SPA (cookie) ;
  - intervention/image **v4** avec le driver GD.
- **Frontend** (`frontend/`, pas encore commencé) : monorepo pnpm avec `apps/web` (React Router v7 en SSR), `apps/admin` (SPA) et `packages/{ui,api-client,i18n,config}`.
- **Ancien poste de développement** (Windows d'entreprise). À vérifier sur le nouveau poste :
  - PowerShell était bloqué par une stratégie de groupe : on utilisait Git Bash ;
  - PHP venait de Laragon, avec `pdo_pgsql` et `pgsql` activés ; pas d'imagick, de pcntl ni de phpredis ;
  - Docker n'était pas utilisable : cache, file d'attente et sessions utilisent le driver `database` ;
  - le shell bash de ce poste altérait les antislashs dans les heredocs : écrire les fichiers PHP avec les outils d'édition, pas avec des heredocs.
- Bases : `wariss_dev` (développement) et `wariss_dev_test` (tests). Sur un nouveau poste, crée-les, remplis `.env` à partir de `.env.example`, puis lance `composer install` et `php artisan migrate --seed`.
- CI : `.github/workflows/backend.yml` lance Pint, Larastan et Pest sur PostgreSQL 18.

## 4. Ce qui est FAIT (commité, poussé, CI verte)

Dernier commit : `1f58fa4 feat(api): CRUD d'administration de tous les contenus éditoriaux`. **462 tests réussis**, Larastan et Pint propres, badge de la CI `passing`.

### 4.1 Base de données : complète, 86 tables
Tous les domaines D1 à D18 :
- profil, réglages, navigation, pages et sections ;
- technologies et compétences, projets, études de cas, services, laboratoire, blog, parcours, certifications, chronologie ;
- témoignages, messages de contact, newsletter, médias, CV et versions ;
- SEO, redirections, journal d'activité, mesure d'audience.

Les contraintes CHECK sont nombreuses. `model:prune` est planifié à 03:00, avec les durées de conservation de `config/wariss.php` : messages 24 mois, journal 12 mois, statistiques brutes 90 jours.

Conventions des migrations et des modèles :
- `$table->id()->generatedAs()`, `timestampsTz()`, `softDeletesTz()` ;
- contraintes CHECK écrites avec `DB::statement` et des valeurs en dur ;
- modèles dans `app/Models/<Domaine>/`, sauf `User` qui reste dans `App\Models` ;
- énumérations créées avec `make:enum MediaKind`, sans préfixe de dossier ;
- chaque modèle polymorphe ou journalisé est ajouté à la **morph map stricte** dans `AppServiceProvider` ;
- lancer `composer ide-helper` après chaque nouveau modèle ;
- un champ jsonb qui exige un objet a besoin d'un mutateur qui écrit `{}` : PHP encode un tableau vide en `[]`.

### 4.2 Socle de l'API v1
- Préfixe `/api/v1` avec trois groupes : `auth`, `public` (throttle `public-read`) et `admin` (`auth:sanctum` et `EnsureCanWrite`, qui met le `viewer` en lecture seule).
- Connexion et déconnexion, compte connecté (`Auth/SessionController`, `Auth/CurrentUserController`). La connexion est bloquée après 5 échecs ; `last_login_at` et le journal d'activité sont renseignés.
- Gate `view-sensitive-data` : réservée à `admin`.
- Filet de sécurité dans `bootstrap/app.php` :
  - `QueryException` 23514 ou 23502 → 422 « Les données envoyées ne respectent pas les règles de ce contenu. » ;
  - 23505 → 409 « Un contenu identique existe déjà. ».
- Dans les tests HTTP Sanctum, il faut `->withHeader('Referer', 'http://localhost:5174')`.

### 4.3 CRUD d'administration (29 ressources, 159 routes)
- **`AdminCrudController`** (`app/Http/Controllers/Api/V1/Admin/`) gère :
  - liste paginée (`per_page` jusqu'à 100) avec filtres et tris spatie ;
  - détail, création, modification (PATCH partiel) et suppression ;
  - `restore` et `forceDestroy` pour les contenus qui ont une corbeille.

  Points d'extension : `model()`, `formRequest()`, `relations()`, `children()`, `beforeSave()`, `detailRelations()`, `filters()`, `sorts()`, `defaultSort()`.

  Une suppression bloquée (LogicException, ou SQLSTATE 23503 ou 23001) renvoie **409**, dans une `DB::transaction`.
- **`App\Actions\Admin\SaveModel`** fait tout en une transaction : champs, traductions, synchronisation des relations, remplacement des enfants, redirection 301 (D-06) et journal d'activité.
- **`AdminFormRequest`** fournit les aides suivantes :
  - `requiredOnCreate`, `routeId` ;
  - `finalValue($modelClass, $field)` : valeur envoyée, sinon valeur en base ;
  - `translationRules`, `sluggedTranslationRules`, `childRules`, `idsRules` ;
  - `safeUrl`, `safeLink`, `mediaRule`, `countryCodeRule`, `localeRule` ;
  - `editorialRules`, `displayRules`, `slugFormat`, `uniqueTranslatedSlug`.
- **`AdminResource`** renvoie :
  - les attributs du modèle ;
  - les traductions regroupées par langue ;
  - les relations plusieurs-à-plusieurs sous forme `xxx_ids` ;
  - les enfants sous forme d'objets.
- Routes déclarées dans `routes/api/v1/admin.php` avec le helper `$crud($uri, Controller::class, softDeletes: bool)`.
  - Contenus avec corbeille : projects, case-studies, services, experiments, posts, experiences, pages.
- Ressources : technology-categories, technologies, skill-categories, skills, project-categories, projects, case-studies, service-categories, services, experiment-categories, experiments, post-categories, tags, posts, experiences, educations, certifications, achievements, timeline-events, testimonials, newsletter-campaigns, pages, page-sections, navigation-menus, navigation-items, social-links, media-collections, redirects, resumes.
- Règles métier déjà en place :
  - Post : l'auteur est le compte connecté et le temps de lecture est calculé.
  - Témoignage : approbation impossible sans consentement, et retrait du consentement refusé sur un témoignage approuvé.
  - Page : seules des pages `custom` peuvent être créées ; une page système ne peut être ni supprimée ni renommée.
  - Élément de menu : seule la destination qui correspond au type est gardée, et le parent doit appartenir au même menu.
  - Expérience : `is_current` vide `ended_on`.
  - Lien social : rattaché au profil, 422 si aucun profil n'existe.
  - Redirection et campagne : `created_by` est renseigné.
  - Redirection : contrôle de boucle et de source externe.
  - CV : la version courante est choisie uniquement parmi les versions de ce CV.
  - Chronologie : la source doit exister et être du même type.
- Tests : `tests/Feature/Api/Admin/AdminCrudTest.php` (47) et `AdminDomainRulesTest.php` (17).

## 5. EN COURS : bibliothèque de médias (D14), NON COMMITÉ

> ⚠️ Ces fichiers n'existent **que sur l'ancien poste** tant qu'ils ne sont pas commités et poussés. S'ils manquent sur le nouveau poste, réécris-les en suivant cette description.

**Fichiers écrits, pas encore testés :**

1. `backend/config/wariss.php`, nouvelle section `uploads` :
   ```php
   'uploads' => [
       'types' => [ // extension => types MIME réels acceptés, nature, taille max en Ko
           'jpg'  => ['mimes' => ['image/jpeg'], 'kind' => 'image', 'max_kb' => 10_240],
           'jpeg' => ['mimes' => ['image/jpeg'], 'kind' => 'image', 'max_kb' => 10_240],
           'png'  => ['mimes' => ['image/png'],  'kind' => 'image', 'max_kb' => 10_240],
           'webp' => ['mimes' => ['image/webp'], 'kind' => 'image', 'max_kb' => 10_240],
           'pdf'  => ['mimes' => ['application/pdf'], 'kind' => 'document', 'max_kb' => 20_480],
           'mp4'  => ['mimes' => ['video/mp4'],  'kind' => 'video', 'max_kb' => 102_400],
           'webm' => ['mimes' => ['video/webm'], 'kind' => 'video', 'max_kb' => 102_400],
       ],
       'image_quality' => 85,
       'max_image_pixels' => 40_000_000,
       'directory' => 'media',
   ],
   ```
   Le SVG est exclu, car il peut contenir du script ; les icônes passent par `icon_key`.

2. `backend/app/Services/Media/MediaStorage.php` :
   - `checksum(UploadedFile)` : SHA-256 du fichier d'origine, pour détecter les doublons.
   - `store(UploadedFile, MediaVisibility, ?MediaKind, array $attributes, ?User $uploader): Media` :
     - nom en UUID, chemin `media/Y/m/<uuid>.<ext>` ;
     - disque `public` si le média est public, `local` (privé, `storage/app/private`) sinon ;
     - **images ré-encodées par GD** : orientation appliquée, EXIF retiré. Appel : `ImageManager::usingDriver(GdDriver::class, autoOrientation: true, strip: true)->decodePath()`, puis `->encode(new JpegEncoder(quality:, progressive: true, strip: true))->toString()`, avec `PngEncoder` ou `WebpEncoder` selon l'extension ;
     - `forceFill` des champs techniques (disk, path, original_name, stored_name, mime_type, extension, size_bytes, checksum_sha256, width, height, kind, visibility, uploaded_by, metadata) ;
     - traductions `alt_text`, `caption` et `description` ;
     - le fichier est supprimé si l'insertion échoue.
   - `changeVisibility(Media, MediaVisibility)` : déplace le fichier d'un disque à l'autre.
   - `deleteFile(Media)`.
   - `url(Media)` : URL externe, URL publique, ou route `admin.media.file` pour un fichier privé. **Cette route n'existe pas encore.**
   - `diskFor(MediaVisibility)`.

3. `backend/app/Services/Media/MediaUsages.php` : `for(Media)` lit dans `pg_constraint` toutes les clés étrangères vers `media`, sauf `media_translations`.
   - Pour chaque usage, il renvoie `table`, `column`, `ids` (20 au plus ; `mediable_id` pour la table `mediables`), `count` et `blocking`.
   - `blocking` vaut vrai pour les règles RESTRICT ou NO ACTION, par exemple `mediables`. SET NULL ou CASCADE ne bloquent pas.

**Contexte du modèle `App\Models\Media\Media`** :
- `fillable` : `media_collection_id`, `kind`, `visibility`, `external_url`, `credit` ;
- champs traduits : `alt_text`, `caption`, `description` ;
- `isExternal()` ;
- contrainte `media_source_check` : soit un fichier (tous les champs techniques remplis), soit une `external_url` sans aucun champ de fichier ;
- `unique(disk, path)` ;
- visibilité `private` par défaut ;
- fabrique avec les états `privateDocument()` et `externalVideo()`.

**Ce qui reste pour les médias (conception prévue) :**
- Dans `AdminCrudController`, ajouter un point d'extension `resourceClass()` (`AdminResource` par défaut), utilisé par index, show, store, update et restore. Passer `deleteOrConflict`, `deleted` et `validated` en `protected`.
- `MediaResource` : le contenu d'`AdminResource::payload()`, plus `url`, plus `usages` dans le détail.
- `MediaController` (`Admin/Media/`), qui hérite du socle CRUD :
  - **Liste** : filtres `kind`, `visibility`, `media_collection_id` et recherche sur `original_name` ; tris `id`, `created_at`, `size_bytes`.
  - **Création** (multipart), deux cas exclusifs :
    - `file` : contrôles de l'extension en liste blanche, du **type MIME réel** (finfo) dans les mimes autorisés, de la taille (`max_kb`), et pour une image de largeur × hauteur ≤ `max_image_pixels`. `kind` vient du type (image peut être déclarée `icon`). **Doublon** : même checksum → 409 avec l'id du média existant, sauf si `allow_duplicate=true`. Le journal d'activité reçoit `Created`.
    - `external_url` : https obligatoire, `kind` obligatoire, `duration_seconds` facultatif ; création par `SaveModel`.
    - Champs communs : `visibility` (par défaut `private`), `media_collection_id`, `credit`, traductions facultatives.
  - **Modification** :
    - métadonnées et traductions par `SaveModel`, sans `visibility` ;
    - si `visibility` change, appel à `MediaStorage::changeVisibility` ;
    - `external_url` et `kind` modifiables seulement pour un média externe (image ↔ icon autorisé pour un fichier).
  - **Suppression** :
    - si un usage bloque → toujours 409 avec la liste des usages ;
    - si des usages existent sans `?confirm=1` → 409 avec la liste ;
    - sinon : suppression en transaction, puis suppression du fichier, puis journal.
  - **`GET media/{id}/file`**, nommée `admin.media.file` : sert le fichier (privé ou public) avec `X-Content-Type-Options: nosniff` et un `Content-Disposition` qui reprend `original_name`. 404 pour un média externe. Le `viewer` peut lire.
- Routes dans `admin.php` : `$crud('media', MediaController::class)`, puis la route `file`.
- Tests avec `Storage::fake('public')`, `Storage::fake('local')` et `UploadedFile::fake()->image(...)` :
  - EXIF retiré ;
  - type refusé (SVG, faux PNG) ;
  - doublon ;
  - changement de visibilité qui déplace le fichier ;
  - suppression bloquée ou confirmée ;
  - fichier privé servi ;
  - `viewer` refusé en écriture.

## 6. CE QUI RESTE À FAIRE (par ordre)

1. **Terminer les médias** (section 5), commit `feat(api): bibliothèque de médias`.
2. **Versions de CV** : téléversement d'un PDF par `MediaStorage`, en privé ou en public selon le cas.
3. **Profil** : ressource unique (GET et PATCH, pas de liste).
4. **Réglages** :
   - le `viewer` ne voit que les réglages publics ;
   - mise à jour par lot ;
   - `social-links` est déjà fait.
5. **Données sensibles** derrière la gate `view-sensitive-data` :
   - messages de contact : lecture, statut, suppression ;
   - abonnés à la newsletter ;
   - journal d'activité en lecture seule ;
   - comptes.
6. **SEO par contenu** (`seo_metadata`, polymorphe) ; **aperçu des brouillons** par URL signée de 30 minutes.
7. **Tableau de bord et statistiques** : compteurs, visites, partages, sources.
8. **API publique** (`routes/api/v1/public.php`), avec des ressources publiques séparées et seulement les contenus publiés et visibles :
   - profil, pages, sections, menus, projets, études de cas, services, laboratoire, articles, parcours, témoignages approuvés ;
   - recherche plein texte ;
   - téléchargement du CV ;
   - **contact** : Turnstile, champ piège (honeypot), throttle ;
   - **newsletter** : double opt-in par e-mail, désinscription par jeton ;
   - **mesure d'audience** : `page_views` et événements `share`, empreinte de session = sel quotidien + IP + UA, **jamais l'IP stockée** ;
   - résolution des redirections, sitemap.
9. **Commandes planifiées** : `content:publish-scheduled` (chaque minute) et `analytics:aggregate` (chaque jour).
10. **Messages de validation en français** (`lang/fr`). Aujourd'hui, par exemple, « The consent given field must be accepted. » s'affiche encore en anglais.
11. **Documentation OpenAPI** avec Scramble : `openapi.json` commité.
12. **Documentation** :
    - décision D-19 (conventions de l'API : erreurs 409 et 422, pagination, format des traductions, médias) dans le registre de l'architecture ;
    - mise à jour de `documentation/04-API-SPECIFICATIONS/`.
13. Ensuite viendra le **frontend** (`frontend/`), à faire uniquement quand l'utilisateur le demandera.

## 7. Commandes utiles (depuis `backend/`)

```bash
php artisan test --compact                   # toute la suite Pest
php artisan test --compact --filter=Media    # tests ciblés
vendor/bin/pint --dirty                      # style
vendor/bin/phpstan analyse                   # Larastan (si « Undefined constant LARAVEL_VERSION » : vendor/bin/phpstan clear-result-cache)
php artisan route:list --path=api/v1/admin
composer ide-helper                          # après un nouveau modèle
```

Pièges connus :
- Le JSON de Pest compte séparément `errors` et `failed` : vérifie les deux.
- N'utilise jamais `DB::raw` directement dans un dataset Pest : enveloppe-le dans une closure.
- Pour tester une contrainte CHECK, passe par `DB::table()->update()` : les casts d'énumération rejettent les mauvaises valeurs avant la base.
- Une erreur SQL dans une transaction PostgreSQL bloque toute la suite de la transaction : enveloppe chaque opération risquée dans sa propre `DB::transaction`.
