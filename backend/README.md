# WARISS.DEV — Backend (API Laravel)

API REST de WARISS.DEV : Laravel 13, PHP 8.4, PostgreSQL. Conception de référence : [`../documentation/`](../documentation/).

## Installation locale

Prérequis : PHP 8.4 avec `pdo_pgsql`, Composer, PostgreSQL (bases `wariss_dev` et `wariss_dev_test`), Node.js (pour `composer dev`).

```bash
cp .env.example .env        # puis renseigner DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate
```

## Commandes

| Commande | Rôle |
| --- | --- |
| `composer dev` | Serveur (http://localhost:8000) + file d'attente |
| `composer test` | Tests Pest (base `wariss_dev_test`) |
| `composer lint` / `composer format` | Vérifier / corriger le style (Pint) |
| `composer analyse` | Analyse statique (Larastan, niveau 6) |
| `composer check` | Style + analyse + tests |

## Environnement local

- Sessions, cache et files : pilote `database` (pas de Redis ni de Docker en local).
- E-mails : écrits dans `storage/logs` (ou Mailpit de Laragon, voir `.env.example`).
- Telescope (débogage) : http://localhost:8000/telescope, en environnement `local` uniquement.
