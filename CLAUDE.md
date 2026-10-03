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
   left alone. `DatabaseSeeder` runs in one transaction, so a seed killed part-way leaves the
   table empty and the next boot retries it.
5. Builds assets if the Vite manifest is missing, and removes a stale `public/hot`.

Behaviour is env-driven: `RUN_WORKERS`, `RUN_MIGRATIONS`, `SEED_IF_EMPTY`, `BUILD_ASSETS`,
`INSTALL_DEPS`, `CACHE_CONFIG`, `DB_WAIT_TIMEOUT`, set in `.env` and passed to the container by
`docker-compose.yml` (which passed none of them before 2026-09-29). `DB_FRESH_ON_BOOT` is
deliberately **not** passed through, so a forgotten `true` in `.env` cannot wipe production.
See `docs/DEPLOYMENT.md`.

The entrypoint runs `config:clear` **before** any database step: a cached config on the bind
mount otherwise made the DB wait, migrate and seed run against the previous boot's settings.

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
docker compose exec app php artisan migrate:fresh --seed --force
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
3. **Compute S from the pre-review D, then update D.** Both stability formulas (F6, F7) take the
   difficulty the card had *before* this review, and mean reversion (F5) pulls toward the
   *unclamped* D0(Easy) (about -4.77 with the defaults). This is the order of py-fsrs, fsrs-rs and
   the optimizer that produced the default weights. Until 2026-09-30 this rule said the opposite,
   which shortened Hard intervals ~15% and lengthened Easy ones ~23%.
4. **Hard (2) is a pass.** Only Again (1) routes to the lapse formula. Ratings are 1–4.
5. **Recompute `FACTOR` when `w[20]` changes** — never hard-code it.
6. **Write a `review_logs` row for every review.** No log, no optimizer, ever.
7. **Fuzz only in Review state, only above 2.5 days, only through `FuzzSource`.**
8. **Use the rollover hour** for day arithmetic, not raw 24-hour differences.

Two reference quirks are pinned by tests rather than "fixed", for compatibility:
the fuzz formula can exceed its own upper bound by a day as the random draw
approaches 1, and a **same-day** Hard may reduce stability even though Hard never
can after a day or more.

### Bugs found by auditing PHP against JS, fixed 2026-08-04

Three confirmed, provable divergences — not hypothetical, each reproduced with
concrete inputs before being fixed:

1. **Day-boundary math ignored timezone entirely.** The JS mirror computed
   `dayDifference` in UTC and never accepted a timezone; `sync.js` didn't even pass
   one. For a user in `Asia/Tehran`, the same instant pair gave `1` in PHP and `0`
   in JS — deciding F8 vs F6/F7 differently for the same review. Fixed with
   `Intl.DateTimeFormat`-based local-date extraction, confirmed against PHP
   including a run spanning a US DST transition (14 days either way — DST does not
   perturb the count, in either implementation).
2. **`isNew()` only checked `stability` in JS, not `difficulty`.** A card with
   stability set but difficulty null/undefined was treated as an established review
   card in JS (masked with a fallback difficulty) but correctly re-derived as brand
   new in PHP — S=41.21/D=2.50 vs S=2.31/D=2.12 for the same input.
3. **A persisted `stability` of exactly `0.0` (not null) produced `NaN` in PHP**
   via `pow(0, negative)`, which then propagates forever since every later review
   for that card starts from it. JS "fixed" the same input by substituting
   `DEFAULT_EASE_FACTOR` (2.5) — semantically wrong regardless, since that is an
   ease-factor-scale constant, not a stability value. Both sides now floor/clamp a
   persisted value on read (`max(S_MIN, ...)` / `clamp(D_MIN, D_MAX, ...)`) rather
   than trusting it, in `Scheduler::review()` and its JS mirror.

All three are pinned in `tests/fixtures/fsrs-vectors.json`
(`day_difference`, `corrupted_memory_state`) so they cannot silently regress —
verified by reintroducing each bug one at a time and confirming the fixture tests
fail, then restoring the fix.

Also found live in the API (not the scheduler itself):
`Card::retrievability()` / `UserStaticCardState::retrievability()`
(`HasFsrsMemory` trait) have no route to the card's deck, so they always compute
using the FSRS-6 **defaults** and a plain `diffInDays`, not the deck's own
(possibly optimized) parameters or the user's rollover hour. Confirmed to diverge
materially once a deck has non-default parameters (0.809 vs 0.777 for the same
elapsed time and stability) and near a rollover boundary (a fractional
"0.125 days" from Carbon vs the correct 0). `App\Services\ReviewService::retrievabilityOf()`
resolves the actual per-deck scheduler and is what both `StudyController` and
`StaticDeckController` use now; the trait method remains only as a documented
approximation for contexts with no deck/user in hand.

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

**How the study screens use it (since 2026-09-30).** `public/js/study-session-unified.js`
studies from the device through `window.MemFlash` whenever IndexedDB is available:

1. Online: `sync()` pushes anything queued, then `refreshDeck(type, id)` re-downloads just this
   deck (`GET /api/sync/bootstrap?deck=card:12`) — only when the queue is empty, because a
   refresh replaces the device's copy.
2. `studyQueue(type, id)` picks the cards on the device (`offline/queue.js`, mirroring
   `getCards` / `dueCardsFor`): due reviews, then new cards up to the daily limit. The bootstrap
   sends `new_cards_today` per deck and `study_day_started_at`; the client keeps counting.
3. `rate()` schedules with the mirror, writes the card, and queues the raw rating durably;
   `sync()` then runs in the background. Nothing is lost offline, on reload or on close.
4. The dashboard downloads every deck (`bootstrapIfStale`, at most every 6 h, only with an empty
   queue) and asks the service worker to precache every study page plus the versioned study
   scripts (listed in a `memflash-offline-assets` meta tag), so an unopened deck works offline.

Without IndexedDB, or for a deck the server will not hand over (someone else's public deck,
`refreshDeck` → 404), the screen falls back to the direct endpoints (`/api/study/*` batch
saves). CSRF for the JSON calls comes from the `XSRF-TOKEN` cookie, with one retry after a 419,
because a page served from the service worker's cache carries a stale meta token.

Tests: `offline/queue.test.mjs` (pure) and `offline/sync.test.mjs`, which runs the real
`sync.js` + `db.js` against `offline/fake-indexeddb.mjs` with a scripted server.

### The optimizer

`fsrs:optimize` fits the 21 weights per deck from that deck's own log. Queued
(`OptimizeFsrsParameters`) because it is CPU-bound, unique per deck, weekly by
schedule. Needs **400** usable reviews minimum, 1000 for a reliable fit; "usable"
excludes each card's first review and same-day repeats.

Gradients are **numerical** (central differences), not analytic — slower, but the
derivation is where hand-written optimizers go quietly wrong. A fit that does not
beat the defaults is discarded. See `docs/DEPLOYMENT.md` for the worker and cron setup.

### Word lookup and word lists (since 2026-10-03)

The dashboard's "Look up a word" panel (`x-ui.word-lookup`, Alpine) looks a word
up and saves it to a list. It is deliberately compact: no card form, the card is
the translation chip and meaning the user taps (one input appears only when a
lookup leaves a side empty).

`/lookup?q=word` is the same panel on its own page, opened on that word, so
another app (the user's subtitle reader) links a word in with
`https://<host>/lookup?q=%%SS&sl=en&tl=fa`. It takes Google Translate's parameters:
`sl`/`tl` fix the direction (`Direction::fromQuery()`; none or `sl=auto` detects),
and `text=` works as `q=`. The page keeps the address bar in step with
the direction switch. For links from other apps, `AuthMiddleware` uses
`redirect()->guest()` and the Google callback `redirect()->intended()`, so a
signed-out user lands back on the word. Each word on a list's page links to it.
There is no link out to Google Translate or any other site; the user removed it.

**A list is a deck and a saved word is a card.** There is no list table: lists are
studied, scheduled, exported and synced offline with no code of their own, and
every deck the user owns can be picked as a list. Don't build a parallel model.

- `decks.is_default_list` marks the one deck words go into before the user picks
  another. A Postgres **partial unique index** (`decks_one_default_list_per_user`)
  allows one per user, which makes `WordListService::defaultListFor()`'s
  `firstOrCreate` race-safe. It is created on first use ("My Words"), never just
  by showing the dashboard; deleting it is allowed and the next save recreates it.
- The list picked last is `users.preferences.word_list_id` (JSON, no FK); a stale
  or foreign id falls back to the default list. `POST /api/word-lists/words` takes
  `list_id` = an id, `"default"`, or nothing (= the remembered list). The browser
  always sends an id or `"default"`: the picker remembers a pick in a separate
  request, and a save with no `list_id` could overtake it.
- Ownership is `DeckPolicy::update`, the same as adding a card by hand.

**Providers are configuration.** `App\Services\WordLookup\WordLookupService` asks
ordered provider lists from `config/services.php` → `word_lookup` (env-driven, see
`docs/DEPLOYMENT.md`): `DefinitionProvider`s (Wiktionary, Datamuse) for English
meanings, `TranslationProvider`s (Google Cloud with a key, MyMemory without) for
English ↔ Persian. First answer wins; a throwing provider is logged and skipped.

A lookup has a `Direction`: `en-fa` (meanings + Persian), `fa-en` (an English word
only), or `en-en` (an English dictionary: meanings only, no translator is called,
and the chosen meaning is the card's **back**, its example the note). Without one
it is detected by script (`\p{Arabic}` → `fa-en`, else `en-fa`). What picking a
meaning puts on the card is decided server-side per sense
(`LookupResult::senseCard()`, sent as `senses[i].back`/`.note`); the browser only
applies it, so keep that logic out of the JS. Results are cached per direction,
30 days when complete, 10 minutes when a part is missing (quota/outage, not
"unknown word"); bump the key version (`word-lookup:vN:`) whenever
`LookupResult::toArray()` changes shape.

`Text::clean()` normalizes Arabic ي/ك to Persian ی/ک and strips translation-memory
punctuation; it uses `/u` regexes, **never `trim()` with a character list**, whose
bytes overlap Persian letters.

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
`ReviewService`, `DeckFileProcessor`, `DeckCsvExportService`, `WordListService`,
and `WordLookup\WordLookupService` with its providers.

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
- **The study JS exists twice.** `resources/js/study-*.js` is Vite-bundled but **never loaded**;
  the views load `public/js/study-*.js`. Both copies are currently identical — edit both, or
  better, convert the study screens to the bundled module and delete the `public/js` copies.
- **The static study screen never calls its own queue endpoint.** It studies from the device
  (see Offline); its server-rendered `window.studyConfig.cards` is only the no-IndexedDB fallback,
  so `/api/static-study/{deck}/cards` is still unused.
- The answer buttons now show real intervals (from the mirror, or the queue payload's
  `intervals`). Parts 8–9 of the spec (the review screen, card browser, statistics, and the
  Archivo / Source Serif / IBM Plex Mono design system with decay-curve sparklines) are
  **not built**.
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

### Fixed on 2026-09-29 — do not "re-fix"

- **The study screen only saved when every card ended on Easy.** Only Easy removed a card and
  ratings were sent in one batch at session end, at one instant, with no `client_uuid`. Each
  rating is now sent as it is given (with a uuid, so retries dedupe). The server's
  `scheduled_seconds` decides whether a card comes back in-session: (re)learning and due within
  20 min. A failed save stays queued and blocks the "Amazing Work!" screen.
- **A new card's first rating was ignored** (every rating gave Learning step 0 in 60s). It now
  applies to the steps as in py-fsrs: Easy graduates, Good goes to step 1. Pinned in
  `fsrs-vectors.json` → `new_card_transitions`, PHP and JS.
- **"Cards per day" capped the whole queue per page load.** It is now a limit on *new* cards per
  study day (`ReviewService::newCardsLeftToday`, counted from `review_logs.state_before = new`
  since the user's rollover). Due reviews are always shown, up to `MAX_REVIEWS_PER_SESSION` (200).
- **Static progress counted ratings**, so re-reviewing 10 cards "completed" a 50-card lesson. It
  counts first reviews only and is capped at the lesson size.
- `/admin` returned 403 to everyone outside `APP_ENV=local`: `User` now implements `FilamentUser`.
- Blocking a user did nothing: `status` was not fillable and never checked. `AuthMiddleware` and
  `GoogleController` now refuse blocked users. Logout now invalidates the session.
- Offline sync applied reviews older than the card's last review (negative elapsed, NaN R). They
  are rejected as superseded; future timestamps are clamped to now; the scheduler (PHP and JS)
  never uses negative elapsed days.
- The optimizer clamped 3 of 21 weights and could ship w7 < 0; all 21 now use fsrs-rs bounds.
  The job no longer overwrites a previous fit with the defaults when a new fit does not win.
- The SM-2 backfill took `last_review` from a column holding the due date; a corrective
  migration moves it back by the interval (rows where `last_review >= due` only).
- `cards.front`/`back` were varchar(255) under a 1000-character form limit; now `text`.
- CI's `branches: ['*']` skipped any branch with a `/`; now `'**'`.
- `.dockerignore` now excludes `.env*`, which was baked into image layers.

- **Fixed 2026-09-30:** stability took the *post-update* D and mean reversion aimed at the
  *clamped* D0(Easy); both now follow py-fsrs (rule #3). `single_review`, `repeated_good` and
  `corrupted_memory_state` in the fixture were recomputed with an independent transcription of
  py-fsrs that first reproduced the old values exactly. Existing cards keep their stored S/D; only
  reviews from then on use the corrected order.

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
