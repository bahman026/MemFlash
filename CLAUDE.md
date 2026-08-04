# MemFlash — Working Notes

Flashcard / spaced-repetition app. English → **Persian (Farsi)** vocabulary trainer built around the
*American English File* textbook series (Starter + Files 1–5), plus user-created decks.

**Stack:** Laravel 12 · PHP 8.4 · PostgreSQL 17 · Livewire 3 · Filament 4 · Tailwind 4 + Vite 7 ·
Socialite (Google-only auth) · PhpSpreadsheet (CSV/XLSX import).

---

## Docker dev workflow

Services (`docker-compose.yml`): `app` (php-fpm), `webserver` (nginx), `db` (postgres), `redis`.
All ports bind to `127.0.0.1` only.

| Service | Host URL / port |
|---|---|
| App | http://127.0.0.1:5050 |
| Postgres | `127.0.0.1:5052` (db `memflash_db`, user `memflash`) |
| Redis | `127.0.0.1:5051` |

```bash
docker compose up -d
docker compose logs -f app
docker compose exec app php artisan <cmd>
docker compose exec db psql -U memflash -d memflash_db
```

### The entrypoint is now non-destructive (was not, before 2026-08-04)

`docker/entrypoint.sh` runs on **every** container start, so it must never be destructive. It used to
run `migrate:fresh --seed --force`, which dropped every table on each `docker compose up` and wiped
all user decks/cards/progress. It now:

1. Generates `APP_KEY` only when it is empty.
2. Runs `migrate --force` — pending migrations only, existing rows preserved.
3. Seeds **only when `static_decks` is empty**, so a fresh DB self-bootstraps and a seeded one is
   left alone.

Both guards matter. Plain `migrate` alone is not enough: every `StaticCard*Seeder::seedVocabulary()`
writes `interval => 1, revised_at => null, last_reviewed => null`, so an unconditional `db:seed`
resets every user's SRS scheduling on the shared `static_cards` rows even without `migrate:fresh`.

To deliberately rebuild from scratch (**local dev only — destroys all data**):

```bash
DB_FRESH_ON_BOOT=true docker compose up -d
```

First boot still takes a few minutes to seed ~5,000 cards; nginx returns 502 until php-fpm logs
`ready to handle connections`.

### ⚠️ The entrypoint is baked into the image — edits need a rebuild

`Dockerfile:58` does `COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh`. The project volume
mount does **not** cover it, so editing `docker/entrypoint.sh` has no effect until:

```bash
docker compose build app && docker compose up -d
```

Verify which version is live with
`MSYS_NO_PATHCONV=1 docker compose exec app grep -n migrate /usr/local/bin/entrypoint.sh`.

(On Git Bash, `MSYS_NO_PATHCONV=1` is required for any absolute container path, or it gets rewritten
to a Windows path such as `C:/Program Files/Git/usr/local/bin/...`.)

`.dockerignore` must exclude `public/storage` — `artisan storage:link` points it at an absolute
in-container path, so it dangles on the host and the build fails with `invalid file request
public/storage`.

### ⚠️ Unstyled pages? Check for a stale `public/hot`

`npm run dev` writes `public/hot`, and its presence makes `@vite()` load assets from the Vite dev
server (e.g. `http://[::1]:5173/resources/css/app.css`) instead of `public/build`. If the dev server
was killed without a clean shutdown the file is **left behind**, so every page ships broken asset
URLs and renders as raw unstyled HTML.

```bash
ls public/hot && rm public/hot      # then hard-refresh the browser
```

Diagnose by grepping the served HTML for `5173`. `public/hot` is gitignored and purely transient —
deleting it is always safe. Use `npm run build` for a static setup; only keep `public/hot` while
`npm run dev` is actually running.

### Other env notes

- `REDIS_HOST=127.0.0.1` is wrong for Docker (should be `redis`), but nothing uses it: cache,
  session, and queue are all `database`. Redis is running but idle.
- Admin access is a **single hardcoded email**, `ADMIN_EMAIL` (default `admin@memflash.dev`), with
  `ADMIN_PASSWORD`. There is no role/permission model.
- Vite assets are not built by the entrypoint. `npm install && npm run build` (or `npm run dev`) on
  the host; `public/build` is volume-mounted in.

---

## Code conventions

- **`declare(strict_types=1)` in every PHP file** — Pint enforces it (`declare_strict_types: true`).
- Format with `./vendor/bin/pint`; CI runs `pint --test` and **fails on any diff**.
- Static analysis: `./vendor/bin/phpstan --memory-limit=2048M analyse` (larastan, level 5, `app/` only).
- Tests: `./vendor/bin/pest`.

CI (`.github/workflows/deploy-application.yml`) runs on push to **every** branch: composer install →
npm build → pint --test → pest → phpstan. Despite the name it only builds an artifact; it does not deploy.

**Testing reality:** only the two stock `ExampleTest` stubs exist. There is zero coverage of any
route, controller, policy, or service. Tests run against in-memory SQLite (`phpunit.xml`) while dev
and prod are Postgres.

---

## Architecture

### Two parallel, deliberately separate card hierarchies

```
User ──* Deck ──* Card                       user-owned, per-user SRS state
User ──* UserStaticDeckProgress ──1 StaticDeck ──* StaticCard    global curriculum
User ──* UserStaticDeckSetting  ──1 StaticDeck
```

**`Deck` / `Card`** — user-created or CSV/XLSX-imported. SRS state lives on the user's own `cards` row.
Guarded by `DeckPolicy` (`view` allows owner *or* `is_public`; `update`/`delete` owner-only).

**`StaticDeck` / `StaticCard`** — the shipped curriculum, seeded from code. **Critical:** SRS columns
(`interval`, `ease_factor`, `repetitions`, `revised_at`, `last_reviewed`) live on the **shared**
`static_cards` rows, *not* per user. Per-user state is only `user_static_deck_progress`
(`cards_studied` / `total_cards` / `progress_data`) and `user_static_deck_settings` (`cards_per_day`).
So one user studying a static deck — or hitting `POST /static-decks/{deck}/reset` — rewrites the
scheduling every other user sees. The code acknowledges this ("they're shared, not per-user").
Treat any new static-deck feature as global-state mutation.

### Levels

`UserLevelEnum` (`starter`, `elementary`, `pre_intermediate`, `intermediate`, `upper_intermediate`,
`advanced`) drives which static decks a user sees. It is rendered into **SQL `enum` columns** by
migrations via `array_column(UserLevelEnum::cases(), 'value')` on both `users.level` and
`static_decks.level` — **adding a case requires a new migration**, not just an enum edit.

### Seeders

`DatabaseSeeder` → `AdminSeeder`, `StaticDeckSeeder` (66 decks), then `StaticCardStarterSeeder` +
`StaticCardFile1..5Seeder` (one per level, ~5,000 cards, 12/12/12/10/10/10 lessons).

Each `StaticCardFile*Seeder` follows the same shape: fetch decks for one level → one `if` block per
lesson → `seedLessonN()` holds a `$vocabulary` array literal → shared private `seedVocabulary()`
does `updateOrCreate`. Card rows are `['front' => 'win', 'back' => 'پیروز شدن', 'pronunciation' => '/wɪn/']`.

Quirks to know before touching them:
- **Starter/File1/File2 silently discard `pronunciation`** (~2,465 cards get `audio = null`).
  File3/4/5 store it as `audio => ['pronunciation' => ...]`. Same data, two behaviours.
- `updateOrCreate` keys on `(static_deck_id, front, back)` with **no backing unique index**, so
  editing a translation creates a duplicate rather than updating.
- Re-seeding **resets SRS scheduling** on every run.
- `StaticCardSeeder.php` (1,039 lines) is **dead legacy code** — not registered in `DatabaseSeeder`.
  It looks decks up by `lesson_number` without a level filter, which is the bug the `File*` split fixed.
- `StaticDeckSeeder` never sets `category` or `sort_order`, so `StaticDeck::scopeByCategory()` matches
  nothing and `scopeOrdered()` degrades to name-only ordering.

### HTTP layer

Routes are all in `routes/web.php` — **there is no `routes/api.php`**. The `/api/study/*` and
`/api/static-study/*` endpoints are web routes: session auth + CSRF, not a stateless guard.

Auth is `App\Http\Middleware\AuthMiddleware` applied **by FQCN** in route groups (not via an alias).
`AdminAccess` *is* aliased as `admin.access` in `bootstrap/app.php` but that alias is unused — it's
actually applied through `AdminPanelProvider::authMiddleware()`.

No Form Requests (validation is inline in controllers), no Jobs/queues (CSV import up to 10 MB /
2,000 cards runs synchronously in-request), no Actions. Two services: `DeckFileProcessor`,
`DeckCsvExportService`.

Filament admin (`/admin`) covers **only** `User`, `Deck`, `Card`. The entire static-content tree is
seeder/DB-only.

### Views

`resources/views/components/` is the design system (`x-ui.*`, `x-layouts.*`, `x-study.*`).
Deck creation really happens through `x-ui.modals.deck-create-modal` on the dashboard (3 modes:
empty / from file / import into existing), **not** a `decks/create` page.

Study JS is **duplicated**: `resources/js/study-*.js` is Vite-managed but the layout loads
unbundled copies from `public/js/` via `asset()`. Edit both, or consolidate. `public/js/` also
vendors `alpine.min.js` and `tailwind.min.js`.

---

## Known landmines

Pre-existing issues found while mapping the codebase — not things I introduced. Ask before fixing
any of these; several are load-bearing on assumptions I can't verify.

### Still open

- **Static decks are global mutable state** (see the Architecture section). No authorization on
  `StaticDeckController`, no level check, and studying rewrites shared rows. Fixing this properly
  means moving static-card SRS state into a per-user table — a schema change, not a patch.
- **No pagination anywhere** — dashboard, `decks.show`, and all static-deck views `get()`/`load()`
  collections that can reach 2,000 cards.
- **`cards.interval` is nullable with no default**, and `CardController::store` creates cards with
  only `front`/`back`, so manually added cards have `interval = NULL`.
  `SpacedRepetitionService` floors it to 1, but a migration adding `default(1)` would be cleaner.
- **`App\Livewire\DeckList` is dead code** — the only Livewire component, never mounted. Queries
  *all* decks globally (not scoped to the user) and renders `$deck->title`/`$deck->description`,
  neither of which is a column.
- **Orphaned views:** `welcome.blade.php` (277 lines — the `welcome` *route* renders `pages.index`
  instead), `pages/homepage.blade.php`, `components/index.blade.php`, `components/sections/index.blade.php`.
  `components/README.md` documents a structure that no longer matches the tree.
- **Study JS is duplicated** between `resources/js/` (Vite) and `public/js/` (unbundled, actually loaded).
- **`StaticCardSeeder.php`** (1,039 lines) is unregistered legacy code, superseded by the `File*` split.
- **Test coverage is still two stock stubs.** Factories now exist for every model, so this is unblocked.

### Fixed on 2026-08-04 — do not "re-fix"

- `Deck::hasReachedCardLimit()` compared against a **non-existent `max_cards` column** (always null →
  `count() >= 0` → always true), which blocked card creation on every deck. Now uses
  `DeckLimits::USER_DECK_MAX_CARDS`; `max_cards` removed from `$fillable`.
- `StudyController` had **all `authorize()` calls commented out** ("Temporarily disable authorization
  for debugging") → IDOR. Restored; mutating endpoints use the `update` ability (not `view`, which
  also passes for other people's public decks), and `batchUpdate` now authorizes every card **before**
  the try/catch, since `AuthorizationException` extends `Exception` and would be swallowed as a 500.
- `Deck::resetLearningProgress()` **did not exist** → `POST /decks/{deck}/reset` was a 500. Added.
- `resources/views/decks/create.blade.php` **did not exist** → `GET /decks/create` was a 500 for
  logged-in users, and the site footer links there. Added (JS-free; CSV import stays in the dashboard modal).
- SM-2 was **copy-pasted in 4 places**; extracted to `App\Services\SpacedRepetitionService`. That also
  fixed two latent bugs: `$now->addDay()` mutated the shared Carbon instance so `last_reviewed` was
  written as the *next due date* (compounding across `batchUpdate` loops), and a null `interval` /
  zero `ease_factor` collapsed all future intervals to 0, leaving cards permanently due.
- `StaticDeckController::getCards()` applied **no daily limit**, bypassing `cards_per_day`. Now shares
  a `cardsPerDayFor()` helper with `study()`.
- `DeckLimits::USER_MAX_DECKS` was **never enforced**; `DeckController::store` now checks it.
- `AdminAccess` used `env()` at runtime (returns null under a cached config). Now `config('app.admin_email')`.
- `AdminSeeder` passed `email_verified_at` / `status` to `firstOrCreate`, but neither is in
  `User::$fillable` so **both were silently dropped** (verified: the row gets NULL / the column
  default). Now `forceFill`ed on creation.
- `GoogleController` used `->stateless()`, skipping OAuth `state` verification (CSRF). Removed. Also
  now sets `email_verified_at`, which `firstOrCreate` could never set for the same fillable reason.
- Added the 4 **missing factories** (`StaticDeck`, `StaticCard`, `UserStaticDeckProgress`,
  `UserStaticDeckSetting`) — `::factory()` used to throw, which blocked writing tests.
- All six `StaticCard*Seeder`s now **persist `pronunciation`** into `audio` (Starter/File1/File2 parsed
  and discarded it, ~2,465 cards) and **no longer reset `interval`/`revised_at` on re-seed**, so
  re-running a seeder keeps study progress.
- **`pint --test` and `phpstan` both passed CI-clean** afterwards (PHPStan went 18 errors → 0; the
  pre-existing ones were mostly missing relation generics, now annotated).

> Note: Filament's `UserForm` was **not** storing plaintext passwords — `User::casts()` maps
> `password => 'hashed'`, which hashes on save and does not re-hash an existing hash (verified).
> The field was only tidied (`dehydrated` when filled, required on create only).

---

## Ground rules

- **Never commit or push without asking.** Explicit standing instruction from the user.
