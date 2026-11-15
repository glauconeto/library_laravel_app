# Setup & development guide

A detailed guide for getting this project running from a fresh clone, plus troubleshooting and a production deployment checklist. For a condensed version see [README.md](../README.md); for contributor conventions see [AGENTS.md](../AGENTS.md).

## What this project is

Library is a single-tenant library system. Users browse a catalog, borrow books (max 3 active loans, 14-day term), and leave a star rating only after returning the book — unless they are librarians, who can manage the catalog and loans without limits.

### Stack

Laravel 12 · Inertia.js · React 19 (TypeScript, strict) · Vite · Jetstream/Fortify (auth) · Spatie Laravel Permission · PostgreSQL 18 · Laravel Sail (Docker Compose)

### Architecture map

- **Pages** are `.tsx` files under `resources/js/pages/`; `resources/js/app.tsx` resolves them with a glob (`./pages/**/*.tsx`). No `.jsx` files exist.
- **Vite alias** `@` → `resources/js`. Globals `Ziggy` and `route` are typed in `resources/js/globals.d.ts`.
- **Blade** shell (`resources/views/app.blade.php`) injects `@routes` (the `Ziggy` object), the Vite entry (`app.tsx`) and the theme-init script (renders before React to avoid a flash of wrong theme).
- **Controllers** are resource controllers in `app/Http/Controllers`. They authorize via Spatie permissions:

  | Role      | Permissions                                             |
  |-----------|---------------------------------------------------------|
  | `reader`  | `view books`, `view loans`, `create feedback`, `manage feedback` |
  | `librarian`| all of the above **plus** `manage books`, `manage loans` |

  Roles/permissions are seeded by `PermissionsSeeder` + `RolesSeeder` (included in `DatabaseSeeder` for the dev database, and re-seeded on every test run by `tests/TestCase.php`).
- **Flash messages**: actions redirect with `->with('success'|'error', …)` and `FlashMessage.tsx` renders them from the Inertia shared props. JSON/AJAX requests receive the equivalent 422 responses.
- **Inertia responses vs JSON**: the app is 100% Inertia — do not convert controllers to plain JSON without migrating the frontend too.

## 1. Prerequisites

- Docker Engine with the Compose v2 plugin (`docker compose version`)
- git
- Free ports: `80` (app), `5173` (Vite HMR), `5533` (Postgres published on the host)
- PHP, Composer, Node and PostgreSQL **do not** need to be installed on the host — Sail ships all of them.

## 2. First-time setup (verified commands)

```bash
git clone https://github.com/glauconeto/library_laravel_app.git library
cd library

# 1) create a container-oriented .env (never commit it)
cat > .env <<'EOF'
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=library
DB_USERNAME=bob
DB_PASSWORD=bob_1234
EOF

# 2) start containers (first run builds the sail image, takes a while)
./vendor/bin/sail up -d --build

# 3) install dependencies inside the containers
./vendor/bin/sail composer install
./vendor/bin/sail npm ci

# 4) generate the app key (rewrites only APP_KEY inside .env)
./vendor/bin/sail artisan key:generate

# 5) create the schema AND the demo users/roles
./vendor/bin/sail artisan migrate --seed

# 6) one-time: create the database used by the test suite
./vendor/bin/sail exec pgsql createdb -U bob testing

# 7) verify everything works — expect "Tests: 177 passed"
./vendor/bin/sail artisan test
```

Open http://localhost and sign in:

| User      | Password   | Role      |
|-----------|------------|-----------|
| `librarian@example.com` | `password` | `librarian` |
| `reader@example.com`    | `password` | `reader`    |

> If port 80 is busy: `APP_PORT=8080 ./vendor/bin/sail up -d` and open http://localhost:8080 (remember to set `APP_URL=http://localhost:8080` in `.env`).

## 3. Everyday commands

```bash
./vendor/bin/sail up -d                     # start containers
./vendor/bin/sail down                      # stop containers
./vendor/bin/sail artisan migrate           # apply pending migrations
./vendor/bin/sail artisan optimize:clear    # clear config/views/cache after odd behavior
./vendor/bin/sail artisan tinker            # REPL
./vendor/bin/sail npm run dev               # Vite dev server with HMR (port 5173)
./vendor/bin/sail npm run build             # production frontend build into public/build
./vendor/bin/sail artisan test              # full test suite (pgsql/testing)
./vendor/bin/sail artisan test --filter Auth
```

Frontend changes: use `sail npm run dev` during development; run `sail npm run build` before validating production behavior. `public/build` is gitignored.

TypeScript must stay strict-clean: `npx tsc --noEmit`.

## 4. Troubleshooting

**Every page returns 500 with a pgsql connection error**
The web process inside the container does **not** inherit the `environment:` block from `docker-compose.yml` — it reads the repo's `.env` instead. `.env` must point at the `pgsql` service (`DB_HOST=pgsql`, `DB_PORT=5432`), not at host addresses like `127.0.0.1:5533`. After editing `.env`, run `./vendor/bin/sail artisan optimize:clear` and reload.

**Blank page / "Unable to locate file in Vite manifest"**
The frontend was never built. Run `./vendor/bin/sail npm run build`. Dev mode alternative: `./vendor/bin/sail npm run dev`.

**Tests fail with `database "testing" does not exist`**
Run, once: `./vendor/bin/sail exec pgsql createdb -U bob testing`.

**"It wiped my dev database!"**
The suite can't: `tests/TestCase.php` forces `DB_DATABASE=testing` via `putenv`, `$_ENV` and `$_SERVER` (container env would otherwise win), and `phpunit.xml` pins `DB_CONNECTION=pgsql` / `DB_DATABASE=testing` with `force="true"`. Never change these without understanding this.

**PostgreSQL refuses to start / data "disappeared"**
The `pgsql` container runs `postgres:18-alpine`; PG18's data directory is `/var/lib/postgresql/18/docker`, so the volume `sail-pgsql` must be mounted at `/var/lib/postgresql`. Do **not** change the mount path and **never** `docker volume rm library_sail-pgsql` without a backup — it holds the dev database. To start completely fresh: `sail down && docker volume rm library_sail-pgsql && sail up -d --build && sail artisan migrate --seed` (destroys all dev data).

**Host `php artisan …` can't connect to the database**
Expected by design. Use Sail for anything that touches the DB. Host-only commands that are safe: `npx tsc --noEmit`, `npm run build`.

**Login fails after setup**
Did you run `--seed`? Without it there are no users nor roles. `sail artisan db:seed` creates the demo accounts (see table above).

**The seed wiped/left unexpected data in `library`**
`DatabaseSeeder` only creates roles/permissions and the two demo users — it is safe on an empty dev database; don't run it in production.

## 5. Production deployment checklist

Sail is a development environment. Production does not need it, but the app itself is production-ready with the following steps. A dedicated build pipeline (CI) is a nice follow-up but not required.

1. **Server requirements**: PHP 8.2+, Composer, Node.js (build only), PostgreSQL 18, a web server (nginx/Apache) serving `public/` over HTTPS.
2. **Deploy the code** (git pull on the server, or a CI artifact).
3. **`.env` on the server — never commit it**:
   ```ini
   APP_NAME=Library
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.example
   APP_KEY=…          # generate a NEW one: php artisan key:generate --show
   DB_CONNECTION=pgsql
   DB_HOST=your-db-host
   DB_PORT=5432
   DB_DATABASE=library
   DB_USERNAME=library_prod
   DB_PASSWORD=…      # strong, unique
   SESSION_DRIVER=file
   ```
4. **Install and build**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci             # reproducible install from the committed lockfile
   npm run build      # produces public/build (required by @vite in the Blade shell)
   ```
5. **Database**:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan view:cache
   ```
   Do **not** run `route:cache` — `routes/web.php` uses closure routes, which make it fail; convert them to controllers first if you want route caching.
   Do **not** run `db:seed` in production (it would create the demo users with a known password).
6. **Permissions**: the web user must be able to write to `storage/` and `bootstrap/cache/`.
7. **Web server**: document root = `public/`; rewrite to `index.php`; HTTPS with a valid certificate; HSTS; `APP_DEBUG=false` protects against leaking stack traces.
8. **PostgreSQL 18 ops**: run on its own cluster/volume with a strong password and limited access from the app host; schedule `pg_dump` backups; never reuse the development volume.
9. **Smoke test after deploy**: `/login` returns 200; sign-in with a provisioned account; `/` redirects appropriately; check `storage/logs/laravel.log` on the first requests.

## 6. Conventions and further reading

- [AGENTS.md](../AGENTS.md) — branching/commit conventions, agent workflow, validation checklist, project-specific cautions.
- [README.md](../README.md) — quick start.
- Tests live in `tests/Feature` (authorization, loans, books, feedback, dashboard, auth). Run them through Sail; see "Running tests" above.

## Known follow-ups (not yet done)

- CI pipeline (e.g. GitHub Actions) running the pgsql test suite on every PR.
- Production-optimized Dockerfile if you want to deploy as a container image.
