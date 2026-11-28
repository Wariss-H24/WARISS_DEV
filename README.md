# WARISS.DEV

Plateforme personnelle professionnelle (portfolio, services, blog, laboratoire, terminal interactif, CV, contact) avec un CMS hybride privé.

**Stack :** API Laravel + PostgreSQL + Redis · site public React Router v7 (rendu serveur) · administration React (SPA) · authentification Sanctum par cookie · français et anglais.

**État (28/11/2026) :** backend terminé pour la V1 : base de données (86 tables), API d’administration, API publique, formulaires, mesure d’audience, contrat OpenAPI. Décisions D-01 à D-19. Prochaine étape : le frontend.

## Documents de référence

| Document | Rôle |
| --- | --- |
| [Cahier des charges général (v1.1)](documentation/00-VISION/cahier-des-charges-general.md) | Référence fonctionnelle : 18 domaines, pages, routes, lots de livraison |
| [Structure de la base de données](documentation/02-DATABASE/structure-base-de-donnees.md) | 86 tables, conventions, index, ordre des migrations |
| [Architecture technique](documentation/03-TECHNICAL-ARCHITECTURE/architecture-technique.md) | Backend, frontend, dépendances, [registre des décisions](documentation/03-TECHNICAL-ARCHITECTURE/architecture-technique.md#8-registre-des-décisions) |
| [Conventions de l’API](documentation/04-API-SPECIFICATIONS/conventions-api.md) | Accès, langue, formats, public, formulaires, audience, aperçu, médias ; détail dans `backend/openapi.json` |
| [Comptes et mots de passe](documentation/03-TECHNICAL-ARCHITECTURE/comptes-et-mots-de-passe.md) | Création des comptes, hachage, commande `app:set-password`, politique de mot de passe |
| [Archive : cahier des charges du 14/11/2026](documentation/99-ARCHIVE/cahier-des-charges-general_2026-11-14.md) | Remplacé par la v1.1, conservé pour l'historique |

## Organisation de la documentation

Décision D-05 (21 novembre 2026) : cette arborescence est la seule référence.

```text
documentation/
├── 00-VISION/
│   └── cahier-des-charges-general.md        # référence fonctionnelle (v1.1)
├── 01-FUNCTIONAL-SPECIFICATIONS/            # une fiche par page ou module, au besoin
├── 02-DATABASE/
│   └── structure-base-de-donnees.md         # 86 tables, conventions, index, migrations
├── 03-TECHNICAL-ARCHITECTURE/
│   └── architecture-technique.md            # backend, frontend, dépendances, décisions
├── 04-API-SPECIFICATIONS/                   # conventions de l'API ; le détail est généré (OpenAPI)
├── 05-DESIGN-SYSTEM/                        # identité visuelle, couleurs, typographie, composants
├── 06-TESTING/                              # stratégie de tests, accessibilité, performance
├── 07-DEPLOYMENT/                           # environnements, déploiement, sauvegardes
└── 99-ARCHIVE/                              # documents remplacés
```

Règles :

- Un document ne contredit jamais un document de niveau supérieur sans décision écrite.
- Toute décision structurante reçoit un numéro (D-xx) et est inscrite dans l'architecture technique, section 8.
- Les fiches de `01-FUNCTIONAL-SPECIFICATIONS/` ne sont écrites que lorsqu'un module en a besoin avant son développement.

## Organisation du dépôt

Un seul dépôt (décision D-12) :

```text
WARISS_DEV/
├── backend/          # API Laravel 13 : REST, PostgreSQL, files, tâches planifiées
├── frontend/         # monorepo pnpm : apps/web, apps/admin, packages/* (à venir)
├── documentation/    # conception
└── README.md
```
