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

### The entrypoint is self-managing, and non-destructive (it was not, before 2026-08-04)

`docker/entrypoint.sh` runs on **every** container start, so it must never be destructive. It used to
run `migrate:fresh --seed --force`, which dropped every table on each `docker compose up` and wiped
all user decks/cards/progress. It now prepares everything and hands PID 1 to **`pm2-runtime`**, which
supervises `php-fpm`, `queue` (`queue:work`) and `scheduler` (`schedule:work`).

**There is no crontab anywhere.** `schedule:work` is a long-running process that calls `schedule:run`
itself every minute, and PM2 keeps it alive. Scheduled tasks live in `routes/console.php`.

Deploying is `docker compose up -d --build`. Nothing else.

1. Generates `APP_KEY` only when it is empty.
2. **Waits for Postgres** — `depends_on` waits for the container, not for the server to accept
   connections, so migrating immediately is a race on a cold boot.
3. Runs `migrate --force` — pending migrations only, existing rows preserved.
4. Seeds **only when `static_decks` is empty**, so a fresh DB self-bootstraps and a seeded one is
   left alone.
5. Builds assets if the Vite manifest is missing, and removes a stale `public/hot`.

Behaviour is env-driven: `RUN_WORKERS`, `RUN_MIGRATIONS`, `SEED_IF_EMPTY`, `BUILD_ASSETS`,
`INSTALL_DEPS`, `CACHE_CONFIG`, `DB_WAIT_TIMEOUT`, `DB_FRESH_ON_BOOT`. See `docs/DEPLOYMENT.md`.

Two things that will bite you if changed carelessly:

- **`docker/pm2.config.cjs` must stay `.cjs`.** `package.json` declares `"type": "module"`, so a
  `.js` config is parsed as ESM and `module.exports` throws.
- **Timeout ordering:** job 900s < worker `--timeout` 960s < PM2 `kill_timeout` 980s. All three
  derive from one constant in that file. Break the order and a long optimization gets killed
  part-way.
- **php-fpm logs to files, not stdout** (`storage/logs/php-fpm.log`). The base image points
  `error_log`/`access.log` at `/proc/self/fd/2`, which php-fpm cannot open when its stderr is a PM2
  pipe — it crash-loops with `failed to open error_log`. `docker/php/zzz-logs.conf` redirects both.

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

### Scheduling is FSRS-6, and the scheduler is pure

`app/Fsrs/` implements FSRS-6 — the algorithm Anki ships by default. It has **no
database, no framework and no clock**: the timestamp is a parameter and randomness
arrives through an injectable `FuzzSource`. That is what makes it testable against
the published reference vectors with no I/O, so keep it that way.

| File | Role |
|---|---|
| `Fsrs.php` | Formulas F1–F8 |
| `Parameters.php` | 21 weights, derived `FACTOR`, clamp table |
| `Scheduler.php` | State machine (learning/relearning steps), fuzz, rollover day math |
| `SchedulerConfig.php` | One deck's preset |
| `SchedulerFactory.php` | Resolves a `Scheduler` for a deck + user |
| `CardSnapshot` / `ReviewOutcome` | Immutable in/out |
| `Optimizer/` | Fits the 21 weights to logged history |

`App\Services\ReviewService` is the only thing that writes: it runs the scheduler,
saves state and appends the `review_logs` row in one transaction.

**Non-negotiables, in rough order of damage if broken:**

1. **Never persist retrievability.** Always derive it from stability and elapsed days.
   There is a test asserting the column does not exist.
2. **Observe `elapsed_days` and R before mutating** the card. Using post-update state
   silently corrupts every later interval.
3. **Update D before S.** Both stability formulas take the *new* difficulty.
4. **Hard (2) is a pass.** Only Again (1) routes to the lapse formula. Ratings are 1–4.
5. **Recompute `FACTOR` when `w[20]` changes** — never hard-code it.
6. **Write a `review_logs` row for every review.** No log, no optimizer, ever.
7. **Fuzz only in Review state, only above 2.5 days, only through `FuzzSource`.**
8. **Use the rollover hour** for day arithmetic, not raw 24-hour differences.

Two reference quirks are pinned by tests rather than "fixed", for compatibility:
the fuzz formula can exceed its own upper bound by a day as the random draw
approaches 1, and a **same-day** Hard may reduce stability even though Hard never
can after a day or more.

### Two card hierarchies, both with per-user memory

```
User ──* Deck ──* Card                                    memory on the card row
User ──* UserStaticCardState ──1 StaticCard ──1 StaticDeck    memory per user
User ──* UserStaticDeckProgress / UserStaticDeckSetting ──1 StaticDeck
```

**`Deck` / `Card`** — user-created or CSV/XLSX-imported. Memory lives on the `cards`
row, since the deck belongs to one person. Guarded by `DeckPolicy` (`view` allows
owner *or* `is_public`; `update`/`delete` owner-only). Review endpoints authorize
**`update`**, not `view`, because reviewing writes and `view` passes for other
people's public decks.

**`StaticDeck` / `StaticCard`** — the shipped curriculum, seeded from code.
`StaticCard` holds **no memory state at all**; it lives per user on
`user_static_card_states`. Before 2026-08-04 those columns were on the shared
`static_cards` rows, so one learner studying — or resetting — rewrote everyone
else's schedule. `StaticDeck::resetLearningProgressFor(User)` takes a user for
exactly that reason.

Both models use the `HasFsrsMemory` trait, so the scheduler only ever sees a
`CardSnapshot`.

**Presets live in two places** and `SchedulerFactory` is the only thing that knows:
`deck_configs` for personal decks, `user_static_deck_settings` for static decks
(because their schedule is per user). A null `parameters` means "use the defaults".

### Offline

The client downloads its working set once, studies with no network, and queues raw
ratings. **The server is authoritative.**

- `resources/js/fsrs/fsrs.js` mirrors the PHP scheduler so the answer buttons can
  label intervals with no round trip.
- The queue stores the **raw rating + timestamp**, never computed state, so a
  drifting mirror cannot corrupt stored scheduling.
- `POST /api/sync` replays through the PHP scheduler; its result overwrites local state.

> ⚠️ **The JS mirror and PHP must not diverge.** Both are pinned to
> `tests/fixtures/fsrs-vectors.json`. If you change a formula in one, change it in
> the other and run **both** suites (`pest` and `npm run test:js`). CI runs both.

Replay is idempotent through `review_logs.client_uuid` (unique), applied
oldest-first, and preserves the original timestamp.

### The optimizer

`fsrs:optimize` fits the 21 weights per deck from that deck's own log. Queued
(`OptimizeFsrsParameters`) because it is CPU-bound, unique per deck, weekly by
schedule. Needs **400** usable reviews minimum, 1000 for a reliable fit; "usable"
excludes each card's first review and same-day repeats.

Gradients are **numerical** (central differences), not analytic — slower, but the
derivation is where hand-written optimizers go quietly wrong. A fit that does not
beat the defaults is discarded. See `docs/DEPLOYMENT.md` for the worker and cron setup.

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
- All six now persist `pronunciation` into `audio` and use `firstOrNew`, so re-seeding
  updates content **without** touching memory state (which no longer lives on those rows anyway).
- The match key is `(static_deck_id, front, back)` with **no backing unique index**, so
  editing a translation creates a duplicate rather than updating.
- `StaticDeckSeeder` never sets `category` or `sort_order`, so `StaticDeck::scopeByCategory()` matches
  nothing and `scopeOrdered()` degrades to name-only ordering.

### Testing

Tests run on **PostgreSQL** (`memflash_test`), not SQLite — the migrations use
Postgres-native SQL, and testing on a different engine than you deploy on hides
exactly that class of bug.

> ⚠️ **`Tests\TestCase` refuses to run when the config is cached.** With a cached
> config Laravel ignores `phpunit.xml`, so `RefreshDatabase` migrates and truncates
> the **development** database. This wiped 66 decks and 4,999 cards twice during
> development. If the suite aborts, run `php artisan config:clear`. The check sits
> *before* `parent::setUp()` on purpose — `RefreshDatabase` fires from inside it.

`tests/Pest.php` binds `Tests\TestCase` to `Feature` only. Unit tests stay unbound
so the pure scheduler cannot quietly acquire a framework dependency.

Factories are deliberately **not random** where randomness would make tests flaky:
`DeckFactory::$is_public` is `false` with explicit `public()` / `private()` states,
because `DeckPolicy::view()` passes for public decks and a random 30% made every
authorization test fail about one run in three.

### HTTP layer

Routes are all in `routes/web.php` — **there is no `routes/api.php`**. The `/api/study/*` and
`/api/static-study/*` endpoints are web routes: session auth + CSRF, not a stateless guard.

Auth is `App\Http\Middleware\AuthMiddleware` applied **by FQCN** in route groups (not via an alias).
`AdminAccess` *is* aliased as `admin.access` in `bootstrap/app.php` but that alias is unused — it's
actually applied through `AdminPanelProvider::authMiddleware()`.

No Form Requests (validation is inline in controllers), no Actions. Services:
`ReviewService`, `DeckFileProcessor`, `DeckCsvExportService`.

**Queues** use the `database` driver — nothing extra to install. One job so far,
`OptimizeFsrsParameters`. Reviews are scheduled **synchronously** in the request, so
the app works fine with no worker running; only parameter fitting needs one. CSV
import (10 MB / 2,000 cards) is also still synchronous. Scheduled tasks live in
`routes/console.php`, driven by a single `schedule:run` cron entry.
See `docs/DEPLOYMENT.md`.

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

- **No pagination anywhere** — dashboard, `decks.show`, and all static-deck views `get()`/`load()`
  collections that can reach 2,000 cards.
- **⚠️ Offline is NOT reachable from the study screens.** This is the biggest gap between what
  exists and what works. `resources/js/offline/*` and `resources/js/fsrs/*` are bundled into
  `app.js` and exposed as `window.MemFlash`, but the study screens load
  **`public/js/study-session-unified.js`** via `asset()` — an unbundled copy that calls `fetch()`
  directly and never touches `window.MemFlash`. So a study session still fails with no network,
  and in-memory `pendingUpdates` are lost on reload. The engine, the queue, the sync endpoint and
  the service worker all work and are tested; only the UI call sites are unconverted.
- **The study JS exists twice.** `resources/js/study-*.js` is Vite-bundled but **never loaded**;
  the views load `public/js/study-*.js`. Both copies are currently identical — edit both, or
  better, convert the study screens to the bundled module and delete the `public/js` copies.
- **The static study screen never calls its own queue endpoint.** `static-decks/study.blade.php`
  server-renders `$dueCards` into `window.studyConfig.cards`, so `/api/static-study/{deck}/cards`
  (and its `intervals` payload) is unused on that path.
- **The study UI does not show the interval per rating.** Both queue endpoints return
  `intervals` (`{state, days, seconds}` per rating 1–4) and the offline mirror can compute
  them, but no view renders them. Parts 8–9 of the spec (the review screen, card browser,
  statistics, and the Archivo / Source Serif / IBM Plex Mono design system with decay-curve
  sparklines) are **not built**.
- **`StaticDeckController` still has no authorization or level check.** It is no longer
  destructive to other users, but any authenticated user can study any static deck.
- **No `POST /api/cards/{id}/forget` or undo endpoint.** `ReviewService::forget()` and
  `forgetDeck()` exist and are tested; nothing routes to them. Undo must append a
  compensating log row, never delete one.
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
