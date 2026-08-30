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
- Frontend: Inertia.js + React + Vite
- Auth: Fortify + Jetstream + Sanctum
- Permissions: Spatie Laravel Permission
- Database: PostgreSQL
- Preferred local environment: Laravel Sail / Docker Compose

## Important Structure

- `app/` application logic
- `app/Actions/Fortify/` authentication flow actions
- `app/Providers/` auth and bootstrap providers
- `resources/js/` React pages, layouts, and components
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

### Local Environment with Composer/NPM

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
composer test
npm run build
```

### Environment with Sail

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer test
./vendor/bin/sail npm run build
```

## Validation Checklist

Before completing a branch, validate at least what was affected.

### For Auth Changes

- registration
- login
- logout
- password reset
- authenticated route access
- correct redirects

### For Domain Changes

- business rule working
- model relationships without errors
- routes responding correctly
- proper error feedback

### For Frontend Changes

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

## What an Agent Should Avoid

- editing files unrelated to the branch goal;
- mixing refactor with feature in the same commit;
- adding dependencies without clear need;
- changing environment config without documenting;
- reverting user work without explicit request;
- making automatic commits without diff review.

## Suggested Next Work

- `fix/auth-flow`
- `fix/jetstream-delete-user`
- `test/auth-feature-tests`
- `feat/books-crud`
- `feat/loans-flow`
- `feat/user-roles-permissions`

## Definition of Done

A task is done when:

- branch scope is clear;
- changed files make sense for that scope;
- commits tell a coherent story;
- behavior was validated;
- no accidental changes remain in `git status`.