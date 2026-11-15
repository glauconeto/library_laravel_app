# AGENTS.md

## Objective

This repository is a library system built with Laravel 12, Inertia + React, Jetstream, Fortify, Sanctum, and PostgreSQL.

The goal for anyone working here, whether human or agent, is:

- keep `main` stable;
- work on one change per branch;
- make small, coherent commits;
- validate behavior before completing;
- avoid accidental permission changes, local config, or temporary files.

## Stack

- Backend: PHP 8.2+, Laravel 12
- Frontend: Inertia.js + React + TypeScript + Vite
- Auth: Fortify + Jetstream + Sanctum
- Permissions: Spatie Laravel Permission
- Database: PostgreSQL 18
- Preferred local environment: Laravel Sail / Docker Compose

## Important Structure

- `app/` application logic
- `app/Actions/Fortify/` authentication flow actions
- `app/Providers/` auth and bootstrap providers
- `resources/js/` React + TypeScript pages, layouts, and components (`.tsx` only)
- `resources/js/globals.d.ts` global declarations for `Ziggy` and `route`
- `resources/views/` Inertia Blade shell and helper views
- `routes/web.php` Inertia web routes
- `routes/api.php` API routes
- `routes/jetstream.php` Jetstream resource routes
- `tests/` automated tests

## Work Rules

1. Never work directly on `main` for features, refactors, or bugfixes.
2. Create one branch per topic.
3. Execute one step at a time.
4. After completing a step, stop, review the result, then proceed to the next.
5. Avoid large commits with mixed topics.
6. Don't commit `.env`, local temporary files, or editor junk.
7. Don't commit `.codex` without explicit need.
8. Don't change PHP file permissions to executable.
9. Keep `artisan` executable.
10. Before finishing, review `git diff` and `git status`.

## Branch Naming Convention

Use short, clear names:

- `feat/<topic>`
- `fix/<topic>`
- `refactor/<topic>`
- `test/<topic>`
- `chore/<topic>`

Examples:

- `fix/auth-flow`
- `fix/jetstream-delete-user`
- `feat/loans-flow`
- `test/auth-feature-tests`
- `chore/file-permissions`

## Commit Convention

Prefer messages in the format:

- `feat: ...`
- `fix: ...`
- `refactor: ...`
- `test: ...`
- `docs: ...`
- `chore: ...`

Examples:

- `feat: adds loans flow`
- `fix: corrects redirect after login`
- `test: adds auth flow tests`
- `chore: removes execute permission from PHP files`

## Recommended Workflow

### Starting Work

```bash
git switch main
git pull
git switch -c fix/auth-flow
```

### During Work

```bash
git status
git diff
git diff --staged
git add -p
git commit -m "fix: corrects auth flow"
```

### Review Before Completing

```bash
git status
git log --oneline --decorate -n 10
git diff main...HEAD
```

## Useful Project Commands

Prefer Sail for everything. The project `.env` targets the Sail network (`DB_HOST=pgsql`), so host-direct `php artisan` / `composer test` cannot reach the database from outside Docker.

### Environment with Sail

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail exec pgsql createdb -U bob testing   # one-time: required by the test suite
./vendor/bin/sail artisan test                         # full feature suite (pgsql, testing db)
./vendor/bin/sail npm run build                        # also validates the TS build
./vendor/bin/sail artisan tinker                       # REPL inside the container
```

`npx tsc --noEmit` must stay clean (strict mode). There are no `.jsx` files under `resources/js/`; pages are resolved as `./pages/**/*.tsx`, see `resources/js/app.tsx`.

### Host-side (not supported for the database)

Host-direct commands only work for things that do not touch the database (e.g. `npx tsc`, `npm run build`). Anything hitting Postgres must run through Sail.

## Validation Checklist

Before completing a branch, validate at least what was affected.

### For Auth Changes

- registration
- login
- logout
- password reset
- authenticated route access
- correct redirects
- `./vendor/bin/sail artisan test --filter AuthTest`

### For Domain Changes

- business rule working
- model relationships without errors
- routes responding correctly
- proper error feedback
- `./vendor/bin/sail artisan test`

### For Frontend Changes

- `npx tsc --noEmit` clean
- `npm run build` ok
- page opens without error
- Inertia navigation works
- form submits correctly
- error messages appear

## Project-Specific Cautions

### 1. Jetstream

If modifying Jetstream, review:

- `app/Providers/JetstreamServiceProvider.php`
- `routes/jetstream.php`
- features actually enabled in `config/jetstream.php`

Don't assume installed scaffold is complete. Validate real flow.

### 2. User and Loan Rules

If modifying the `User` model, review impact on:

- authentication
- roles/permissions
- loan relationships
- `canBorrow()` rule

### 3. File Permissions

If `100755` appears on regular PHP files, fix before completing:

```bash
chmod 644 path/to/file.php
```

### 4. PostgreSQL 18 and Volume

The `pgsql` service runs `postgres:18-alpine`. Its data directory is `/var/lib/postgresql/18/docker`, so the named volume `sail-pgsql` must be mounted at `/var/lib/postgresql` (not `/var/lib/postgresql/data`). Do not "downgrade" the mount path and do not remove the volume without explicit confirmation — it holds the dev database.

### 5. Environment Files

- `.env` is never committed. In this repo it targets the Sail network (`DB_HOST=pgsql`), not the host.
- `phpunit.xml` pins `DB_CONNECTION=pgsql` and `DB_DATABASE=testing`; the `testing` database must be created once in the Sail Postgres (see Useful Project Commands).
- The web process inside the container does not inherit the Compose `environment:` variables and reads `.env` instead — keep its DB values pointing at the `pgsql` service.

## What an Agent Should Avoid

- editing files unrelated to the branch goal;
- mixing refactor with feature in the same commit;
- adding dependencies without clear need;
- changing environment config without documenting;
- changing `.env` to point at host-reachable addresses (the container does not inherit the Compose environment and reads `.env` instead);
- removing or recreating docker volumes;
- reverting user work without explicit request;
- making automatic commits without diff review.

## Suggested Next Work

Recently completed: TypeScript frontend migration (strict-clean, `.tsx` only), feature test suite on the Inertia contract (`test/feature-suite`, 177 tests on pgsql), PostgreSQL 18 volume fix, README + docs/setup.md (fresh-clone guide & production checklist).

- `fix/jetstream-delete-user`
- `test/e2e` — optional browser-level smoke coverage
- `api/v1` — only if a real API consumer appears (Sanctum already installed)

## Definition of Done

A task is done when:

- branch scope is clear;
- changed files make sense for that scope;
- commits tell a coherent story;
- behavior was validated;
- no accidental changes remain in `git status`.