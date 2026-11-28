# Conventions de l'API v1

> Référence pour le frontend (site public et administration). Le détail de chaque route (paramètres, réponses) est dans le contrat généré **`backend/openapi.json`** (Scramble). Ce document explique les règles communes. Décision D-19.

## 1. Groupes et accès

| Préfixe | Accès | Usage |
| --- | --- | --- |
| `/api/v1/auth` | libre, puis session | connexion, déconnexion, compte connecté (`me`) |
| `/api/v1/public` | libre, limité en débit | contenus publiés, formulaires, mesure d'audience |
| `/api/v1/admin` | session Sanctum (cookie) | CMS ; le compte `viewer` n'a que les lectures (GET) |

- **Authentification** : Sanctum en mode SPA. Appeler d'abord `GET /sanctum/csrf-cookie`, puis envoyer le cookie et l'en-tête `X-XSRF-TOKEN`. Les origines autorisées sont dans `FRONTEND_URLS` et `SANCTUM_STATEFUL_DOMAINS`.
- **Rôles** : `admin` a tous les droits. `viewer` est en lecture seule : 403 sur toute écriture. Les **données sensibles** (messages, abonnés, journal, comptes, réglages non publics) lui sont refusées même en lecture. `me` renvoie `can_view_sensitive_data`.
- **Limites** : lecture publique 120/min, formulaires 5/min, mesure d'audience 60/min et connexion 20/min, toutes par adresse IP. Au-delà : 429.

## 2. Langue

- Langue de la requête : `?locale=fr|en`, sinon l'en-tête `Accept-Language`, sinon `fr`. La langue retenue revient dans `Content-Language`.
- **Public** : chaque contenu est renvoyé dans **une** langue, avec repli sur le français si la traduction manque. Le champ `locale` indique la langue réellement servie, et `slugs` donne le slug de chaque langue (sélecteur de langue, `hreflang`).
- **Admin** : toutes les traductions à la fois, sous `translations.fr` et `translations.en`. Le français est obligatoire à la création.
- Les messages d'erreur suivent la langue de la requête.

## 3. Formats de réponse

| Cas | Code | Corps |
| --- | --- | --- |
| Liste paginée | 200 | `{ data: [...], meta: { current_page, last_page, per_page, total } }` (admin : format de pagination Laravel, `links` et `meta`) |
| Élément | 200, ou 201 à la création | `{ data: {...} }` |
| Suppression | 204 | vide |
| Validation | 422 | `{ message, errors: { champ: [messages] } }` |
| Conflit | 409 | `{ message, ... }` : contenu encore utilisé, doublon de média (`existing_id`), média utilisé (`usages`, `requires_confirmation`), version de CV publiée |
| Non publié ou inexistant | 404 | même réponse dans les deux cas (rien ne trahit l'existence d'un brouillon) |
| Formulaire public accepté | 202 | `{ message }` |

- **Pagination** : `?page=` et `?per_page=` (jusqu'à 50 en public, 100 en admin).
- **Admin, filtres et tris** (spatie/query-builder) : `?filter[status]=published&sort=-created_at`. Les filtres et tris autorisés sont propres à chaque ressource : voir OpenAPI.
- **Dates** : ISO 8601 avec fuseau. Les journées des statistiques sont des journées UTC.

## 4. API publique

- Seuls les contenus **publiés** (`status = published` et `published_at` atteinte) et **visibles** sortent.
- Un contenu référencé (mis en avant dans une section, source d'un événement de chronologie, page d'un menu) n'apparaît que s'il est lui-même public.
- **Jamais exposé** : champs internes (statut, dates techniques, auteur, position, visibilité, consentement), médias privés (remplacés par `null`), justificatif d'une formation, et client et liens d'un projet confidentiel.
- Un média public est renvoyé sous la forme `{ id, kind, url, mime_type, width, height, duration_seconds, alt, caption, credit }`.

Routes principales :

- `site` : réglages publics, profil, menus.
- `pages/{clé ou slug}` : page avec ses sections.
- `projects`, `case-studies`, `services`, `lab`, `posts` : liste et `/{slug}`.
- `technologies`, `skills`, `taxonomies/{type}`.
- `experiences`, `educations`, `certifications`, `achievements`, `timeline`, `testimonials`.
- `search?q=` : syntaxe « moteur de recherche » avec mots, `"phrase exacte"`, `-exclusion` et `or`.
- `resume` et `resume/download`.
- `redirect?path=`, `sitemap.xml`, `preview/{token}`.

**Adresses du site** (D-03, D-19) :

| Contenu | Adresse |
| --- | --- |
| Accueil | `/:lang` |
| Page système | `/:lang/<clé>` |
| Page personnalisée | `/:lang/pages/:slug` |
| Contenus | `/:lang/projects/:slug`, `/:lang/case-studies/:slug`, `/:lang/services/:slug`, `/:lang/lab/:slug`, `/:lang/blog/:slug` |

## 5. Formulaires publics

- `POST contact` et `POST newsletter/subscribe` exigent `consent: true`. Ils sont protégés de trois façons :
  - **Cloudflare Turnstile** : envoyer le jeton du widget dans `turnstile_token` ; il est vérifié côté serveur et obligatoire en production ;
  - un **champ piège** `website`, à garder caché et vide ;
  - la limite de 5 envois par minute.
- La newsletter est en **double opt-in** :
  - la page `/:lang/newsletter/confirm?token=…` appelle `POST newsletter/confirm` avec `{ token }` ; le lien est valable 7 jours ;
  - la page `/:lang/newsletter/unsubscribe?id=…&token=…` appelle `POST newsletter/unsubscribe`.
- La réponse à l'inscription est la même quel que soit l'état de l'adresse.

## 6. Mesure d'audience (sans cookie)

- Le **navigateur** appelle :
  - `POST track/view` à chaque page, avec `{ path, locale, referrer, utm_source, utm_medium, utm_campaign, content_type, content_id }` ;
  - `POST track/event` pour `cta_click`, `demo_open`, `outbound_click` (`target`) et `share` (`network` obligatoire : linkedin, x, facebook, whatsapp, email, copy_link).
- Les liens de partage portent `utm_medium=share`.
- La réponse est toujours 204. Les robots, les visiteurs qui envoient `DNT: 1` ou `Sec-GPC: 1`, et les comptes de l'administration connectés ne sont pas comptés.
- La requête et l'ancre du chemin sont supprimées. L'IP n'est jamais stockée : seule une empreinte quotidienne l'est.
- `cv_download`, `contact_submit` et `newsletter_subscribe` sont enregistrés par le serveur lui-même.

## 7. Aperçu des brouillons (D-09)

1. L'administration appelle `GET /admin/preview-link?type=post&id=12&locale=fr`, qui renvoie `{ token, url, expires_at }` (30 minutes, journalisé).
2. Elle ouvre `url`, c'est-à-dire `/:lang/preview/:token`.
3. Le site appelle `GET /public/preview/{token}`, qui renvoie le contenu quel que soit son statut, avec `is_preview: true`, `Cache-Control: no-store` et `X-Robots-Tag: noindex`. Ces visites ne sont pas comptées.

## 8. Médias (administration)

- `POST /admin/media` en multipart :
  - `file`, contrôlé sur son contenu réel : jpg, png et webp jusqu'à 10 Mo, pdf jusqu'à 20 Mo, mp4 et webm jusqu'à 100 Mo ; le SVG est refusé ;
  - ou `external_url` (https) avec `kind`.
- Les images sont ré-encodées sans EXIF.
- Un doublon renvoie 409 avec `existing_id`, sauf avec `allow_duplicate=1`.
- `visibility` vaut `private` par défaut. La changer déplace le fichier.
- Un fichier se lit avec `GET /admin/media/{id}/file`.
- Suppression d'un média utilisé :
  - 409 avec `usages` et `requires_confirmation` ;
  - `?confirm=1` vide les emplacements ;
  - un usage bloquant (galerie, version de CV) doit d'abord être retiré.

## 9. Contrat et types

- Après toute modification d'une route, d'une requête ou d'une réponse, lancer `php artisan scramble:export` et committer `backend/openapi.json`. La CI échoue s'il n'est pas à jour.
- Le frontend génère ses types avec `pnpm api:types`, qui lance `openapi-typescript` sur `../backend/openapi.json`.
- La documentation interactive est disponible en local sur `/docs/api`.
