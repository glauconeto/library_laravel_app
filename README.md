# Library

A library management system: a book catalog, loans with a 3-active-loan limit, due dates, star ratings on returned books, and role-based access. Built on Laravel 12 with an Inertia + React + TypeScript frontend, Fortify/Jetstream authentication and PostgreSQL 18 running in Docker through Laravel Sail.

## Features

- **Book catalog** with search, genre and availability filters
- **Loans** with a maximum of 3 active loans per user, 14-day default due date and one-week extensions (not allowed when overdue)
- **Feedback** with a 1–5 star rating — only after the book has been returned (librarians may post anytime)
- **Roles**: `reader` and `librarian` (via Spatie Laravel Permission)
- **Dark/light theme** toggle persisted per user
- **TypeScript** frontend, strict-clean (`npx tsc --noEmit`)

## Stack

Laravel 12 · Inertia.js · React 19 · TypeScript · Vite · Jetstream/Fortify (authentication) · Spatie Laravel Permission · PostgreSQL 18 · Laravel Sail (Docker)

## Requirements

- Docker Engine with the Docker Compose v2 plugin
- Nothing else on the host: PHP, Composer, Node.js and PostgreSQL all live inside the containers

## Quick start

```bash
git clone https://github.com/glauconeto/library_laravel_app.git library
cd library

# point the app at the Sail Postgres (details in docs/setup.md)
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

./vendor/bin/sail up -d --build
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed   # creates the schema + demo users
./vendor/bin/sail exec pgsql createdb -U bob testing   # one-time, required by the test suite
./vendor/bin/sail artisan test                          # 177 tests, all green
```

Open http://localhost — sign in as `librarian@example.com` / `password` (librarian) or `reader@example.com` / `password` (reader).

If port 80 is taken, start with `APP_PORT=8080 ./vendor/bin/sail up -d` and open http://localhost:8080.

## Running tests

```bash
./vendor/bin/sail artisan test                # full suite (expects the `testing` database to exist)
./vendor/bin/sail artisan test --filter Auth  # filter by name
```

The test suite runs against **PostgreSQL** in a dedicated `testing` database (never the dev `library` database). `phpunit.xml` pins `DB_CONNECTION=pgsql` / `DB_DATABASE=testing`, and `tests/TestCase.php` re-seeds roles/permissions and forces the `testing` database on every test. Run tests through Sail — the host cannot reach Postgres directly.

## Documentation

- **[docs/setup.md](docs/setup.md)** — detailed local setup, architecture map, troubleshooting and a production deployment checklist
- **[AGENTS.md](AGENTS.md)** — conventions for agents and contributors (branching, commits, validation)
