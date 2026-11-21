# WARISS.DEV — Structure complète de la base de données

Nov 20, 2026 · @Wariss BADA

Le modèle compte 86 tables métier (dont 30 tables de traduction et 9 tables pivots), plus les tables natives de Laravel, réparties sur les 18 domaines du cahier des charges. Les décisions en suspens sont toutes validées (voir la dernière section).

## Conventions

- **Clés** : `id` en bigint identity ; clés étrangères nommées `<table_au_singulier>_id`, déclarées explicitement et indexées.
- **Dates** : `created_at` et `updated_at` (timestamptz) sur toutes les tables, non répétés ci-dessous. `deleted_at` (SD) sur les contenus éditoriaux.
- **Colonnes éditoriales (ED)** : `status` (draft, scheduled, published, archived), `published_at?`, `is_featured` (bool, faux par défaut), `position` (int, 0 par défaut). Un contenu est public seulement si `status = published` et `published_at <= now()`.
- **Catalogues sans cycle éditorial** (technologies, compétences, expériences, etc.) : `is_visible` (bool) et `position` à la place du statut.
- **Énumérations** : `varchar` + contrainte CHECK, en miroir des enums PHP. Pas de type ENUM PostgreSQL, trop rigide à faire évoluer.
- **Traductions** : chaque entité traduisible a une table `<entité>_translations` (`id`, `<entité>_id` en CASCADE, `locale`, unique sur le couple). Les colonnes listées après « Traductions : » vivent dans cette table. Les slugs sont traduits et uniques par `(locale, slug)`. Français par défaut, anglais en second.
- **Suppression** : CASCADE pour traductions et pivots ; RESTRICT pour catégories et médias utilisés ; SET NULL pour les liens facultatifs.
- **JSONB** : uniquement pour la configuration et des détails réellement flexibles.
- **Recherche (D-08)** : colonne `search_vector` (tsvector générée, `STORED`) et index GIN sur les traductions des articles, projets, services et expérimentations. La configuration linguistique dépend de la ligne : `CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END`. Pondération : titre (A), résumé ou extrait (B), contenu (C). Les accents sont ignorés grâce à l'extension `unaccent`, appelée via une fonction `immutable_unaccent()` créée par migration (la fonction native n'est pas `IMMUTABLE`, ce qu'exige une colonne générée).
- **Slugs et suppression douce (D-06)** : un contenu supprimé en douceur (`deleted_at`) **garde son slug réservé**. L'unicité `(locale, slug)` reste donc totale. Cela permet de restaurer un contenu sans conflit et évite qu'une ancienne URL indexée pointe soudain vers un autre contenu. Pour réutiliser un slug, il faut supprimer définitivement l'ancien contenu ou modifier son slug. Quand le slug d'un contenu publié change, une ligne `redirects` (301) est créée automatiquement.
- **E-mails (D-07)** : les adresses sont enregistrées en minuscules (normalisation dans le modèle) et protégées par un index unique sur `lower(email)`, créé par une requête SQL dans la migration. Concerné : `users.email`, `newsletter_subscribers.email`. Le type `citext` n'est pas utilisé, pour éviter une extension de plus et les cas particuliers avec Eloquent.
- **Secrets** : jamais en base, uniquement dans `.env`.
- **Notation** : `?` = nullable ; `→` = clé étrangère ; (SD) = soft delete ; (ED) = colonnes éditoriales.
- **Images** : une image unique et fixe (avatar, couverture, logo, certificat) est une clé étrangère directe `*_media_id` (intégrité garantie) ; les galeries et pièces jointes passent par la table `mediables`.

## Vue d'ensemble

| Domaine | Tables |
| --- | --- |
| D1 Identité et profil | 4 |
| D2 Configuration et navigation | 5 |
| D3 Services | 7 |
| D4 Technologies | 4 |
| D5 Compétences | 6 |
| D6 Portfolio et projets | 7 |
| D7 Études de cas | 5 |
| D8 Parcours | 5 |
| D9 Certifications et réalisations | 5 |
| D10 Chronologie | 2 |
| D11 Laboratoire | 5 |
| D12 Blog | 7 |
| D13 Communication | 6 |
| D14 Médias | 4 |
| D15 CV | 2 |
| D16 Administration, sécurité et SEO | 4 |
| D17 Analytics | 3 |
| D18 Pages et sections | 5 |

## D1 — Identité et profil

- `users` — `name`, `email` (stocké en minuscules, index unique sur `lower(email)` : D-07), `password`, `role` (admin, viewer ; défaut viewer), `is_active` (bool), `last_login_at?`, `email_verified_at?`, `remember_token?`. Comptes d'administration uniquement : aucun compte visiteur n'existe. Deux comptes prévus, créés par un seeder : admin (propriétaire du site) et viewer (lecture seule, pour un ami).
- `profiles` — `user_id→users` (unique, CASCADE), `first_name`, `last_name`, `display_name`, `public_email`, `public_phone?`, `country_code?` (char 2), `avatar_media_id?→media`, `cover_media_id?→media`, `availability_status` (available, limited, unavailable), `available_from?` (date), `spoken_languages` (jsonb, ex. code + niveau). Traductions : `headline`, `short_bio`, `biography` (text), `location_label?`, `availability_note?`.
- `social_links` — `profile_id→profiles` (CASCADE), `platform`, `url`, `is_visible`, `position`.

## D2 — Configuration et navigation

- `site_settings` — `group`, `key` (unique avec `group`), `type` (string, text, bool, int, json, media), `value?` (jsonb), `is_translatable` (bool), `is_public` (bool : seuls les réglages publics sont exposés par l'API). Logo et favicon sont des réglages de type `media`. Traductions : `value` (text).
- `navigation_menus` — `name`, `slug` (unique), `location?` (unique : header, footer, secondary, legal), `is_active`.
- `navigation_items` — `navigation_menu_id→navigation_menus` (CASCADE), `parent_id?→navigation_items` (CASCADE), `type` (page, route, external), `page_id?→pages` (CASCADE), `route_key?` (route frontend fixe : projects, blog, contact…), `url?`, `icon?`, `opens_in_new_tab`, `is_visible`, `position`. CHECK : un seul champ de destination rempli, selon `type`. Traductions : `label`.

## D18 — Pages et sections

- `pages` — `key` (unique : home, about, now, uses, legal-notice, privacy…), `kind` (system, custom), `template`, `status`, `published_at?` (SD). Les pages système ne peuvent pas être supprimées. Traductions : `title`, `slug?` (nul pour les pages système, dont la route est fixe ; unique par langue sinon), `summary?`.
- `page_sections` — `page_id→pages` (CASCADE), `type` (hero, about, skills, featured\_projects, experiences, services, latest\_posts, cta, contact, richtext, gallery, faq), `position`, `is_active`, `config` (jsonb : variante, nombre d'éléments, mise en page). Chaque `type` correspond à un composant React. Traductions : `title?`, `subtitle?`, `body?` (text), `cta_label?`, `cta_url?`.
- `page_section_items` — `page_section_id→page_sections` (CASCADE), `itemable_type`, `itemable_id`, `position`. Contenus mis en avant (projets, services, articles…), unique sur section + type + id. Le contenu n'est jamais recopié.

## D3 — Services

- `service_categories` — `icon?`, `position`, `is_active`. Traductions : `name`, `slug`, `description?`.
- `services` — `service_category_id→service_categories` (RESTRICT), `cover_media_id?→media` (SET NULL), `icon?`, `pricing_model?` (fixed, hourly, monthly, quote), `price_from?` (decimal 12,2), `price_currency?` (char 3), `delivery_min_days?`, `delivery_max_days?` (int), (ED), (SD). Traductions : `title`, `slug`, `summary`, `description` (text), `target_audience?`, `cta_label?`. Tarif et délai affichés seulement s'ils sont renseignés.
- `service_features` — `service_id→services` (CASCADE), `kind` (benefit, included, deliverable), `icon?`, `is_visible`, `position`. Traductions : `label`, `description?`.
- `service_technology` — pivot : `service_id`, `technology_id`.

## D4 — Technologies

- `technology_categories` — `position`, `is_active`. Traductions : `name`, `slug`. Exemples : langages, frameworks, bibliothèques, bases de données, outils, plateformes, infrastructure.
- `technologies` — `technology_category_id→technology_categories` (RESTRICT), `name` (nom de marque, non traduit), `slug` (unique), `icon_media_id?→media` (SET NULL), `icon_key?`, `brand_color?` (char 7), `official_url?`, `proficiency?` (learning, working, proficient, expert), `is_visible`, `is_featured`, `position`. Traductions : `description?`. Une technologie n'existe qu'une fois ; les autres domaines la réutilisent par pivot.

## D5 — Compétences

- `skill_categories` — `position`, `is_active`. Traductions : `name`, `slug`.
- `skills` — `skill_category_id→skill_categories` (RESTRICT), `slug` (unique), `proficiency?` (smallint, CHECK de 1 à 5), `years_of_experience?` (decimal 3,1), `is_visible`, `is_featured`, `position`. Traductions : `name`, `description?`. Une compétence décrit ce que tu sais faire ; une technologie est l'outil utilisé.
- `skill_technology` — pivot : `skill_id`, `technology_id`.
- `project_skill` — pivot : `project_id`, `skill_id`.

## D6 — Portfolio et projets

- `project_categories` — `position`, `is_active`. Traductions : `name`, `slug`.
- `projects` — `project_category_id→project_categories` (RESTRICT), `cover_media_id?→media` (SET NULL), `kind` (professional, freelance, personal, academic, open\_source), `development_stage` (planned, in\_progress, completed, maintained, paused), `client_name?`, `started_on?`, `ended_on?` (date, CHECK fin ≥ début), `live_url?`, `repository_url?`, `demo_url?`, `is_confidential` (bool : masque client et liens), (ED), (SD). Traductions : `title`, `slug`, `summary`, `description` (text), `context?`, `role`, `responsibilities?`.
- `project_features` — `project_id→projects` (CASCADE), `is_visible`, `position`. Traductions : `title`, `description?`.
- `project_technology` — pivot : `project_id`, `technology_id`.

## D7 — Études de cas

- `case_studies` — `project_id?→projects` (SET NULL ; un projet peut en avoir zéro ou plusieurs), `cover_media_id?→media` (SET NULL), `started_on?`, `ended_on?` (date), `reading_time_minutes?` (smallint), (ED), (SD). Traductions : `title`, `slug`, `summary`.
- `case_study_sections` — `case_study_id→case_studies` (CASCADE), `type` (context, need, objectives, constraints, challenge, solution, architecture, results, lessons, custom), `position`. Traductions : `title?`, `body` (text). Remplace les tables `challenges` et `solutions`.
- `case_study_technology` — pivot : `case_study_id`, `technology_id`.

## D8 — Parcours

- `experiences` — `organization_name`, `organization_url?`, `organization_logo_media_id?→media` (SET NULL), `employment_type` (full\_time, part\_time, contract, freelance, internship, apprenticeship, volunteer), `is_remote` (bool), `country_code?`, `started_on` (date), `ended_on?`, `is_current` (bool), `is_visible`, `position` (SD). CHECK : si `is_current`, `ended_on` est nul ; sinon fin ≥ début. Traductions : `job_title`, `location_label?`, `summary`, `description` (text), `achievements?` (text).
- `experience_technology` — pivot : `experience_id`, `technology_id`.
- `educations` — `institution_name`, `institution_url?`, `institution_logo_media_id?→media` (SET NULL), `degree_level` (certificate, diploma, bachelor, master, doctorate, bootcamp, self\_taught, other), `completion_status` (in\_progress, completed, interrupted), `started_on?`, `ended_on?`, `country_code?`, `proof_media_id?→media` (SET NULL, privé par défaut), `is_visible`, `position`. Traductions : `degree_title`, `field_of_study?`, `location_label?`, `description?`. Le champ `completion_status` évite d'afficher un diplôme non obtenu.

## D9 — Certifications et réalisations

- `certifications` — `issuer_name`, `issuer_url?`, `credential_id?`, `verification_url?`, `issued_on` (date), `expires_on?`, `certificate_media_id?→media` (SET NULL), `is_visible`, `is_featured`, `position`. Traductions : `title`, `description?`.
- `certification_technology` — pivot : `certification_id`, `technology_id`.
- `achievements` — `type` (award, distinction, publication, talk, hackathon, open\_source, milestone, other), `achieved_on` (date), `organization_name?`, `url?`, `media_id?→media` (SET NULL), `is_visible`, `is_featured`, `position`. Traductions : `title`, `description?`.

## D10 — Chronologie

- `timeline_events` — `type` (education, experience, certification, project, achievement, learning, event), `event_date` (date), `end_date?`, `eventable_type?`, `eventable_id?` (donnée source), `image_media_id?→media` (SET NULL), `is_visible`, `position`. Traductions : `title?`, `description?`. Quand une source est renseignée, l'affichage reprend ses données et les traductions ne servent que de surcharge facultative ; un événement manuel exige un titre traduit (règle applicative). Aucune copie des autres modules.

## D11 — Laboratoire technique

- `experiment_categories` — `position`, `is_active`. Traductions : `name`, `slug`.
- `experiments` — `experiment_category_id→experiment_categories` (RESTRICT), `cover_media_id?→media` (SET NULL), `kind` (demo, prototype, tool, api\_test, visualization, ui\_experiment, learning), `demo_url?`, `repository_url?`, `video_url?`, `demo_component_key?` (clé d'un composant de démonstration autorisé, liste blanche côté frontend), `experimented_on?` (date), (ED), (SD). Traductions : `title`, `slug`, `summary`, `description` (text), `objective?`, `technical_notes?` (text). Aucun code arbitraire n'est exécuté.
- `experiment_technology` — pivot : `experiment_id`, `technology_id`.

## D12 — Blog

- `post_categories` — `position`, `is_active`. Traductions : `name`, `slug`, `description?`.
- `posts` — `author_id?→users` (SET NULL), `post_category_id→post_categories` (RESTRICT), `cover_media_id?→media` (SET NULL), `type` (article, tutorial, guide, feedback, note, tip, news), `reading_time_minutes?` (smallint), (ED), (SD). Traductions : `title`, `slug`, `excerpt`, `content` (text, Markdown). Une seule catégorie par article ; pas de commentaires publics.
- `tags` — aucune colonne propre. Traductions : `name`, `slug`.
- `post_tag` — pivot : `post_id`, `tag_id`.

## D13 — Communication

- `messages` — `type` (contact, quote, collaboration), `service_id?→services` (SET NULL), `name`, `email`, `subject?`, `body` (text), `details` (jsonb : budget, délai, type de projet pour un devis), `locale` (char 2), `consent_given` (bool), `consented_at?`, `status` (new, read, in\_progress, replied, archived, spam), `read_at?`, `replied_at?`, `internal_note?` (text), `ip_hash?` (char 64) (SD). Purge planifiée : suppression au bout de 24 mois. Une seule table pour contact et devis, sans duplication du formulaire.
- `testimonials` — `author_name`, `author_company?`, `photo_media_id?→media` (SET NULL), `project_id?→projects` (SET NULL), `given_on?` (date), `consent_given` (bool), `status` (pending, approved, rejected), `is_visible`, `is_featured`, `position`. Traductions : `quote` (text), `author_role?`. Publié seulement si `approved` et `consent_given`.
- `newsletter_subscribers` — `email` (stocké en minuscules, index unique sur `lower(email)` : D-07), `locale`, `status` (pending, subscribed, unsubscribed, bounced), `confirmation_token_hash?`, `confirmed_at?`, `consented_at`, `consent_source`, `unsubscribed_at?`, `ip_hash?`. Suppression définitive sur demande.
- `newsletter_campaigns` — `locale`, `subject`, `preview_text?`, `body` (text), `status` (draft, scheduled, sending, sent, failed), `scheduled_at?`, `sent_at?`, `recipients_count` (int), `created_by?→users` (SET NULL). Une campagne est rédigée dans une seule langue : exception volontaire à la règle des traductions.
- `newsletter_campaign_recipients` — `campaign_id→newsletter_campaigns` (CASCADE), `subscriber_id→newsletter_subscribers` (CASCADE), `status` (queued, sent, failed, bounced), `sent_at?`, `error?`, unique sur campagne + abonné.

## D14 — Médias

- `media_collections` — `name`, `slug` (unique), `description?`, `position`.
- `media` — `media_collection_id?→media_collections` (SET NULL), `disk` (local, public, s3 — D-13), `path` (unique avec `disk`), `original_name`, `stored_name`, `mime_type`, `extension`, `size_bytes` (bigint), `width?`, `height?`, `duration_seconds?`, `checksum_sha256` (char 64, détection des doublons, indexé), `kind` (image, video, document, icon, other), `visibility` (public, private ; **private par défaut**), `external_url?` (vidéo hébergée ailleurs), `credit?`, `uploaded_by?→users` (SET NULL), `metadata` (jsonb, `{}` par défaut : variantes générées). Traductions : `alt_text?`, `caption?`, `description?`. Les fichiers privés sont servis par URL signée temporaire. **D-13** : un média est soit un fichier stocké (`disk`, `path`, `original_name`, `stored_name`, `mime_type`, `extension`, `size_bytes`, `checksum_sha256` tous renseignés), soit externe (`external_url` renseignée, sans `disk`, `path`, `stored_name`, `size_bytes` ni `checksum_sha256`) : contrainte `media_source_check`. `width` et `height` sont renseignées ensemble et positives, ou absentes ensemble.
- `mediables` — `media_id→media` (RESTRICT), `mediable_type`, `mediable_id`, `role` (gallery, screenshot, attachment), `position`, unique sur média + type + id + rôle. La suppression d'un média passe par une vérification applicative qui liste tous ses usages (clés directes et `mediables`).

## D15 — CV

- `resumes` — `title`, `target` (general, freelance, employment, other), `locale` (char 2), `status` (draft, active, archived), `is_default` (bool), `is_public` (bool), `current_version_id?→resume_versions` (SET NULL, ajoutée par une migration dédiée une fois les deux tables créées). Index unique partiel : un seul CV public par défaut par langue.
- `resume_versions` — `resume_id→resumes` (CASCADE), `media_id→media` (RESTRICT), `version_number` (int, unique avec `resume_id`), `change_note?`, `created_by?→users` (SET NULL). Seule la version courante est téléchargeable publiquement.

## D16 — Administration, sécurité et SEO

- `activity_logs` — `user_id?→users` (SET NULL), `event` (login, logout, created, updated, published, unpublished, deleted, settings\_changed, password\_reset), `subject_type?`, `subject_id?`, `description?`, `properties` (jsonb : noms des champs modifiés, jamais de mots de passe ni de secrets), `ip_address?` (inet), `user_agent?`. Seulement `created_at`. Conservation : 12 mois.
- `seo_metadata` — `seoable_type`, `seoable_id` (unique ensemble), `robots_index` (bool, vrai), `robots_follow` (bool, vrai), `canonical_url?`, `og_image_media_id?→media` (SET NULL), `schema_type?`, `structured_data?` (jsonb). Traductions : `meta_title?`, `meta_description?`, `og_title?`, `og_description?`. Rattaché aux pages, services, projets, études de cas, expérimentations et articles.
- `redirects` — `from_path` (unique), `to_path`, `status_code` (CHECK : 301, 302, 307, 308), `is_active`, `hits` (int), `created_by?→users` (SET NULL). Le sitemap et le `robots.txt` sont générés, pas stockés.

## D17 — Analytics

- `page_views` — `viewed_at`, `path`, `locale` (char 2), `viewable_type?`, `viewable_id?`, `session_hash` (char 64 : hash quotidien non réversible, sans cookie), `referrer_host?`, `referrer_type` (direct, search, social, other), `utm_source?`, `utm_medium?`, `utm_campaign?`, `device_type` (desktop, mobile, tablet), `country_code?`. Aucune ligne pour les robots ni pour l'administration. Pas de `created_at`/`updated_at`.
- `events` — `occurred_at`, `name` (cv\_download, cta\_click, contact\_submit, newsletter\_subscribe, demo\_open, outbound\_click), `path`, `eventable_type?`, `eventable_id?`, `session_hash`, `properties` (jsonb).
- `analytics_daily_stats` — `stat_date` (date), `metric`, `dimension_type` (vide par défaut), `dimension_value` (vide par défaut), `value` (bigint), unique sur les quatre premières colonnes. Alimente le tableau de bord. Données brutes conservées 90 jours puis purgées ; agrégats conservés.

## Tables natives de Laravel

`password_reset_tokens`, `sessions`, `personal_access_tokens` (Sanctum), `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`. L'authentification de l'administration utilise Sanctum en mode SPA (cookie).

## Relations polymorphes

Les types polymorphes utilisent un alias court (project, post, service…) via la table de correspondance d'Eloquent, jamais le nom complet de la classe.

- `mediables` : médias rattachés aux projets, services, études de cas, expérimentations et articles.
- `seo_metadata` : pages, services, projets, études de cas, expérimentations, articles.
- `page_section_items` : projets, services, articles, compétences, certifications mis en avant.
- `timeline_events` : expériences, formations, certifications, projets, réalisations.
- `activity_logs`, `page_views`, `events` : tout contenu concerné.

## Règles d'intégrité et index

- Index sur toutes les clés étrangères.
- Index partiel `(published_at)` où `status = 'published'` sur les tables éditoriales.
- Unique `(locale, slug)` sur chaque table de traduction qui porte un slug.
- CHECK sur les dates (fin ≥ début), les niveaux (1 à 5), les codes de redirection et la destination des éléments de menu.
- Index GIN sur les colonnes `search_vector`.
- Index unique fonctionnel sur `lower(email)` pour `users` et `newsletter_subscribers`.
- Extension `unaccent` et fonction `immutable_unaccent()` créées par la première migration applicative.
- Tâches planifiées : purge des messages, du journal d'activité et des données analytics brutes ; publication des contenus programmés.

## Ordre de création des migrations

1. Tables natives de Laravel et `users`.
2. `media_collections`, `media`, `media_translations`.
3. Catalogues : catégories, `technologies`, `skills`.
4. `pages` et sections, puis profil, configuration et navigation.
5. Contenus : services, projets, études de cas, expérimentations, articles, parcours, certifications, témoignages.
6. Tables pivots.
7. Communication, CV (puis `current_version_id`), SEO, redirections.
8. Journal d'activité et analytics.
9. Seeders : compte `admin`, compte `viewer`, pages système (dont `now` et `uses`), menus, réglages, catégories de départ.

L'étape 1 crée aussi l'extension `unaccent` et la fonction `immutable_unaccent()` (D-08), nécessaires aux colonnes `search_vector` des étapes suivantes.

## Décisions validées avant les migrations

- Une seule catégorie par projet (`project_category_id`) et par article (`post_category_id`) : validé, aucune table pivot.
- Conservation : messages 24 mois, journal d'activité 12 mois, analytics bruts 90 jours ; statistiques agrégées conservées plus longtemps, sans donnée identifiante : validé.
- Pas de commentaires publics sur le blog : validé.
- Langues : français et anglais, URLs `/fr/...` et `/en/...` : validé.
- Rôles par colonne `users.role` (admin, viewer), sans tables `roles` et `permissions` en V1 (D-04) : validé.
- Slugs réservés pendant la suppression douce, redirection 301 automatique quand un slug publié change (D-06) : validé.
- E-mails normalisés en minuscules avec index unique sur `lower(email)` (D-07) : validé.
- Recherche plein texte selon la langue de la ligne, sans tenir compte des accents (D-08) : validé.
- Aperçu des brouillons par lien signé, sans table supplémentaire (D-09) : validé.
- Pages Now et Uses gérées comme pages système du domaine D18, sans table dédiée : validé.

Prochaine étape : créer le projet Laravel, puis les migrations, modèles et seeders, dans l'ordre ci-dessus. Les schémas MCD/MLD seront générés depuis la base réelle (outil de diagramme sur le schéma PostgreSQL) plutôt que dessinés à la main, pour rester à jour.
