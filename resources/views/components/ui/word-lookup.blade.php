@props([
    // WordListService::payload() of every deck the user owns: any deck takes words.
    'lists' => [],
    // From WordListService::selectedListIdFor(); null = the default list, not created yet.
    'selectedListId' => null,
    // A word to look up as soon as the page opens (/lookup?q=...).
    'query' => '',
    // A Direction value ('en-fa', 'fa-en', 'en-en') or 'auto' to detect (/lookup?sl=&tl=).
    'direction' => 'auto',
    // Keep the address bar's ?q=&sl=&tl= in step with the lookup (the /lookup page).
    'syncUrl' => false,
    // Put the cursor in the search box when there is no word to look up.
    'autofocus' => false,
])

@php
    $config = [
        'query' => $query,
        'direction' => $direction,
        'syncUrl' => (bool) $syncUrl,
        'autofocus' => (bool) $autofocus,
        'lists' => $lists,
        'listId' => $selectedListId,
        // The language a double-clicked word is explained in: 'fa' or 'en'.
        'peekTarget' => app(\App\Services\WordListService::class)->peekTargetFor(auth()->user()),
        'defaultListName' => \App\Services\WordListService::DEFAULT_LIST_NAME,
        'csrf' => csrf_token(),
        'urls' => [
            'lookup' => route('words.lookup'),
            'createList' => route('word-lists.store'),
            'select' => route('word-lists.select'),
            'addWord' => route('word-lists.add-word'),
            'peekTarget' => route('words.peek-target'),
            'list' => route('decks.show', ['deck' => '__ID__']),
        ],
    ];
@endphp

<section x-data="wordLookup(@js($config))" @dblclick="peekAt($event)" @keydown.escape.window="closePeek()"
         @scroll.window.passive="closePeek()" @memflash-peek-target.window="setPeekTarget($event.detail, false)" class="bg-white shadow-sm rounded-xl border border-gray-100" aria-labelledby="word-lookup-title">
    <div class="p-3 sm:p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="word-lookup-title" class="text-base sm:text-lg font-bold text-gray-900">Look up a word</h2>

            <!-- Direction, as Google Translate's sl/tl -->
            <div data-no-peek class="flex flex-wrap gap-1" role="radiogroup" aria-label="Translation direction">
                <template x-for="[value, label, title] in directions" :key="value">
                    <button type="button" role="radio" :aria-checked="direction === value" @click="setDirection(value)" x-text="label" :title="title" :aria-label="title"
                            :class="direction === value ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300'"
                            class="px-2.5 py-0.5 rounded-full border text-xs font-medium transition-colors"></button>
                </template>
            </div>
        </div>

        <form @submit.prevent="lookUp()" role="search" class="flex gap-2">
            <label for="word-lookup-q" class="sr-only">Word to look up</label>
            <input id="word-lookup-q" x-ref="query" type="search" x-model="q" dir="auto" maxlength="100" autocomplete="off" autocapitalize="off" spellcheck="false"
                   placeholder="ahead  ·  پیش رو"
                   class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <button type="submit" :disabled="loading || !q.trim()"
                    class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="loading ? 'Looking up…' : 'Look up'">Look up</span>
            </button>
        </form>
        <p x-show="error" x-text="error" style="display: none;" class="text-sm text-red-600" role="alert"></p>

        <template x-if="result">
            <div class="space-y-3">
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <span class="text-xl font-bold text-gray-900 break-words" dir="auto" x-text="result.query"></span>
                    <span x-show="result.phonetic" class="font-mono text-sm text-gray-500" x-text="result.phonetic"></span>
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400" x-text="languageName(result.language) + ' → ' + languageName(result.target)"></span>
                </div>
                <p x-show="fetchingMore || moreNote" class="-mt-2 text-xs text-gray-400" role="status" aria-live="polite"
                   x-text="fetchingMore ? (answers.local ? 'From the words on this device · getting meanings…' : 'Getting meanings…') : moreNote"></p>

                <!-- The chosen translation goes on the card -->
                <div x-show="result.translations.length" class="flex flex-wrap gap-1.5">
                    <template x-for="text in result.translations" :key="text">
                        <button type="button" @click="useTranslation(text)" :aria-pressed="isChosen(text)" dir="auto" x-text="text" title="Put this on the card"
                                :class="isChosen(text) ? 'bg-primary-600 border-primary-600 text-white' : 'bg-white border-gray-300 text-gray-800 hover:border-primary-400'"
                                class="px-3 py-1 rounded-full border text-sm transition-colors"></button>
                    </template>
                </div>

                <!-- The chosen meaning becomes the card's note -->
                <ul x-show="result.senses.length" class="rounded-lg border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                    <template x-for="(sense, index) in result.senses" :key="index">
                        <li>
                            <button type="button" @click="useSense(index)" :aria-pressed="senseIndex === index"
                                    :class="senseIndex === index ? 'bg-primary-50' : 'hover:bg-gray-50'"
                                    class="w-full text-left px-3 py-1.5 text-sm transition-colors">
                                <span x-show="sense.part_of_speech" class="mr-1 text-xs font-semibold uppercase text-gray-400" x-text="sense.part_of_speech"></span>
                                <span class="text-gray-900" x-text="sense.definition"></span>
                                <span x-show="sense.example" class="block text-xs italic text-gray-500" x-text="'“' + sense.example + '”'"></span>
                            </button>
                        </li>
                    </template>
                </ul>

                <!-- Only when a lookup leaves one side of the card empty -->
                <div x-show="missing">
                    <label for="word-typed" class="sr-only" x-text="missing === 'front' ? 'English word' : languageName(result.target) + ' meaning'"></label>
                    <input id="word-typed" type="text" x-model="typed" maxlength="1000" dir="auto"
                           :placeholder="'Nothing found. Type the ' + (missing === 'front' ? 'English word' : languageName(result.target) + ' meaning')"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <form @submit.prevent="addWord()" class="flex gap-2">
                    <label for="word-list" class="sr-only">List</label>
                    <select id="word-list" x-ref="listSelect" @change="pickList($event.target.value)"
                            class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <template x-if="!lists.some((list) => list.is_default_list)">
                            <option value="" :selected="listId === null" x-text="defaultListName + ' (default)'"></option>
                        </template>
                        <template x-for="list in lists" :key="list.id">
                            <option :value="list.id" :selected="list.id === listId" x-text="listLabel(list)"></option>
                        </template>
                        <option value="new">+ New list…</option>
                    </select>
                    <button type="submit" :disabled="adding || !cardFront || !cardBack"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-text="adding ? 'Adding…' : 'Add to list'"></span>
                    </button>
                </form>

                <!-- New list, created from the picker without leaving the page -->
                <div x-show="creatingList" class="flex gap-2">
                    <label for="word-new-list" class="sr-only">New list name</label>
                    <input id="word-new-list" x-ref="newListName" type="text" x-model="newListName" maxlength="255" dir="auto" placeholder="New list name"
                           @keydown.enter.prevent="createList()" @keydown.escape.prevent="cancelNewList()"
                           class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <button type="button" @click="createList()" :disabled="creating || !newListName.trim()"
                            class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-2 rounded-lg text-sm font-semibold disabled:opacity-50">
                        Create
                    </button>
                    <button type="button" @click="cancelNewList()"
                            class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg text-sm font-semibold">
                        Cancel
                    </button>
                </div>
                <p x-show="listError" x-text="listError" class="text-sm text-red-600" role="alert"></p>

                <p x-show="notice" role="status" aria-live="polite" class="text-sm" :class="noticeError ? 'text-red-600' : 'text-green-700'">
                    <span x-text="notice"></span>
                    <a x-show="noticeListId" :href="listUrl(noticeListId)" class="ml-1 font-medium underline hover:no-underline">View list</a>
                </p>

                <p class="text-xs text-gray-400">
                    Double-click any word to see what it means.
                    <a href="{{ route('extension') }}" class="underline hover:text-gray-600">Save words from any site with the Chrome extension.</a>
                </p>
            </div>
        </template>
    </div>

    <!-- A double-clicked word, explained without leaving the lookup on screen -->
    <div x-show="peek" x-cloak style="display: none;" @click.outside="closePeek()" :style="peekStyle" data-peek-target="{{ $config['peekTarget'] }}"
         role="dialog" aria-label="Meaning of the word" aria-live="polite"
         class="fixed z-50 w-72 max-w-[calc(100vw-1rem)] bg-white rounded-xl shadow-xl border border-gray-200 p-3 space-y-2 text-left">
        <template x-if="peek">
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <p class="min-w-0">
                        <span class="font-bold text-gray-900 break-words" dir="auto" x-text="peek.word"></span>
                        <span x-show="peek.phonetic" class="ml-1 font-mono text-xs text-gray-500" x-text="peek.phonetic"></span>
                    </p>
                    <button type="button" @click="closePeek()" aria-label="Close" class="-mt-1 -mr-1 px-1.5 text-lg leading-none text-gray-400 hover:text-gray-700">&times;</button>
                </div>

                <p x-show="peek.loading" class="text-sm text-gray-400">Looking up…</p>
                <p x-show="peek.error" x-text="peek.error" class="text-sm text-red-600"></p>

                <p x-show="peek.translations.length" class="text-base text-gray-900" dir="auto" x-text="peek.translations.join('، ')"></p>
                <ul x-show="peek.definitions.length" class="space-y-1 text-sm text-gray-800 list-disc list-inside">
                    <template x-for="(definition, index) in peek.definitions" :key="index">
                        <li x-text="definition"></li>
                    </template>
                </ul>
                <p x-show="!peek.loading && !peek.error && !peek.translations.length && !peek.definitions.length" class="text-sm text-gray-500">Nothing found.</p>

                <div data-no-peek class="flex items-center justify-between gap-2 pt-1 border-t border-gray-100">
                    <!-- The setting: kept on the account, Persian unless changed -->
                    <div class="flex items-center gap-1" role="radiogroup" aria-label="Show the meaning in">
                        <span class="text-xs text-gray-400">Meaning in</span>
                        <template x-for="[value, label] in [['fa', 'فارسی'], ['en', 'English']]" :key="value">
                            <button type="button" role="radio" :aria-checked="peekTarget === value" @click="setPeekTarget(value)" x-text="label"
                                    :class="peekTarget === value ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300'"
                                    class="px-2 py-0.5 rounded-full border text-xs font-medium transition-colors"></button>
                        </template>
                    </div>
                    <button type="button" @click="openPeek()" class="text-xs font-semibold text-primary-600 hover:text-primary-800">Look up →</button>
                </div>
            </div>
        </template>
    </div>
</section>

<script>
/**
 * The word lookup. State only: the server looks words up, builds the card's
 * note and decides which list a word goes into. The card is what the user
 * picked: a translation chip for the Persian (or English) side, a meaning for
 * the note.
 */
function wordLookup(config) {
    return {
        ...config,
        q: config.query || '',
        direction: config.direction || 'auto',
        // Short labels keep the switch on one line on a phone; the result
        // line spells the direction out.
        directions: [
            ['auto', 'Detect', 'Detect the language'],
            ['en-fa', 'EN → FA', 'English → Persian'],
            ['fa-en', 'FA → EN', 'Persian → English'],
            ['en-en', 'EN → EN', 'English → English: the meaning in English'],
        ],
        loading: false,
        // A device answer is on screen and the server's (with meanings) is on its way.
        fetchingMore: false,
        moreNote: null,
        // Bumped per lookup, so a slow answer to an earlier word is ignored.
        lookupId: 0,
        // The current lookup's answers, merged by render(): the server's, the
        // device's (offline/dictionary.js) and Google Translate's, asked by the
        // browser (offline/google.js) because the server cannot reach a
        // translator from Iran.
        answers: { server: null, local: null, browser: null },
        // The user picked a translation or meaning: a later answer must not undo it.
        touched: false,
        error: null,
        result: null,
        senseIndex: 0,
        front: '',
        back: '',
        description: '',
        pronunciation: null,
        // The card side the lookup could not fill ('front' | 'back'), typed in by hand.
        missing: null,
        typed: '',
        adding: false,
        notice: null,
        noticeError: false,
        noticeListId: null,
        creatingList: false,
        creating: false,
        newListName: '',
        listError: null,
        // The double-clicked word's pop-up: { word, phonetic, translations, definitions, loading, error }.
        peek: null,
        peekStyle: {},
        peekId: 0,

        /** Alpine runs this on mount. */
        init() {
            if (this.q.trim()) this.lookUp();
            else {
                if (this.autofocus) this.$nextTick(() => this.$refs.query.focus());
                this.refreshDictionary();
            }
        },

        /** Keep the words on this device current (at most daily), in the background. */
        refreshDictionary() {
            window.MemFlash?.refreshDictionary?.().catch(() => {});
        },

        get cardFront() {
            return (this.missing === 'front' ? this.typed : this.front).trim();
        },

        get cardBack() {
            return (this.missing === 'back' ? this.typed : this.back).trim();
        },

        /**
         * Three sources at once, each shown as soon as it answers: the words on
         * this device (instant), Google Translate from the browser (about a
         * second) and the server (meanings; can be slow on a poor connection).
         */
        async lookUp() {
            const q = this.q.trim();
            if (!q) return;

            const id = ++this.lookupId;
            const direction = this.direction;
            const current = () => id === this.lookupId;
            this.answers = { server: null, local: null, browser: null };
            this.error = null;
            this.moreNote = null;
            this.syncAddressBar();

            const browser = window.MemFlash?.translateInBrowser?.(q, direction) ?? Promise.resolve(null);
            let serverDone = false;

            browser.then((answer) => {
                if (!current() || !answer) return;
                this.answers.browser = answer;
                this.error = null;
                this.render();
                this.loading = false;
                this.fetchingMore = !serverDone;
            });

            const local = await window.MemFlash?.lookUpOnDevice?.(q, direction);
            if (!current()) return;

            if (local) {
                this.answers.local = local;
                this.render();
            }
            this.loading = !(this.answers.local || this.answers.browser);
            this.fetchingMore = !this.loading;

            try {
                const response = await this.request(`${this.urls.lookup}?${this.directionParams(q)}`);
                const data = await response.json().catch(() => ({}));
                if (!current()) return;

                if (!response.ok) {
                    this.serverFailed(this.errorMessage(response, data), await browser);
                    return;
                }

                this.answers.server = data;
                this.render();
            } catch {
                if (current()) this.serverFailed('Meanings need a connection.', await browser);
            } finally {
                serverDone = true;
                if (current()) {
                    this.loading = false;
                    this.fetchingMore = false;
                }
                this.refreshDictionary();
            }
        },

        /** The server failed: say so, unless another source already answered. */
        serverFailed(message, browserAnswer) {
            const shown = this.answers.local || this.answers.browser || browserAnswer;
            if (shown) this.moreNote = (this.answers.local ? 'From the words on this device. ' : 'Translation only. ') + message;
            else this.error = message === 'Meanings need a connection.' ? 'Could not reach the server. Looking words up needs a connection.' : message;
        },

        /** The current lookup's answers on screen: the server's first, else the device's, else Google's. */
        render() {
            const { server, local, browser } = this.answers;
            const data = server ?? local ?? browser;
            if (!data) return;

            this.show(data, data === local ? null : local, data === browser ? null : browser);
        },

        /**
         * Put a lookup on screen. `local`, the device's answer, and `browser`,
         * Google Translate's, are merged into `data`: the device's translations
         * come first (they are the curriculum's or the user's own), then the
         * server's, then Google's; and what the user already picked stays.
         */
        show(data, local = null, browser = null) {
            const sameWord = this.result !== null && this.result.query === data.query && this.result.direction === data.direction;
            const keepPicks = sameWord && this.touched;
            const translations = [...new Set([...(local?.translations ?? []), ...data.translations, ...(browser?.translations ?? [])])];

            this.result = { ...data, translations, phonetic: data.phonetic ?? local?.phonetic ?? browser?.phonetic ?? null };
            this.typed = sameWord ? this.typed : '';
            this.notice = sameWord ? this.notice : null;

            if (keepPicks) {
                // Senses arrived after a pick: fill only what is still empty.
                if (!this.description) this.description = data.card.description ?? '';
                this.pronunciation ??= data.card.pronunciation ?? local?.card.pronunciation ?? browser?.card.pronunciation ?? null;
            } else {
                this.touched = false;
                this.senseIndex = 0;
                this.front = data.card.front;
                this.back = data.card.back;
                this.description = data.card.description ?? '';
                this.pronunciation = data.card.pronunciation ?? local?.card.pronunciation ?? browser?.card.pronunciation ?? null;

                // The device's translation is a known-good answer: it wins the
                // translated side over a machine translation or an English
                // meaning. Google's fills that side when the server found no
                // translation (its card then holds an English meaning, or nothing).
                const pick = local ?? (browser && data.translations.length === 0 ? browser : null);
                if (pick && data.language === 'fa') this.front = pick.card.front;
                else if (pick) this.back = pick.card.back;
            }

            this.missing = !this.front ? 'front' : !this.back ? 'back' : null;
        },

        /** A new direction applies at once, as on Google Translate. */
        setDirection(value) {
            this.direction = value;
            if (this.q.trim()) this.lookUp();
            else this.syncAddressBar();
        },

        /** q, plus Google Translate's sl/tl unless detecting: "en-en" → sl=en&tl=en. */
        directionParams(q) {
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (this.direction !== 'auto') {
                const [sl, tl] = this.direction.split('-');
                params.set('sl', sl);
                params.set('tl', tl);
            }

            return params.toString();
        },

        languageName(code) {
            return code === 'fa' ? 'Persian' : 'English';
        },

        /**
         * On the /lookup page, mirror the lookup in the URL (?q=later&sl=en&tl=fa)
         * so it can be bookmarked or shared.
         */
        syncAddressBar() {
            if (!this.syncUrl) return;

            const q = this.q.trim();
            const search = this.directionParams(q);
            history.replaceState(history.state, '', location.pathname + (search ? `?${search}` : ''));
            document.title = (q ? `${q} · ` : '') + 'Look up · MemFlash';
        },

        /** English → Persian: a translation is the back. Persian → English: the front. */
        isChosen(text) {
            return (this.result.language === 'fa' ? this.front : this.back) === text;
        },

        useTranslation(text) {
            this.touched = true;
            if (this.result.language === 'fa') this.front = text;
            else this.back = text;
        },

        /**
         * What a meaning puts on the card comes from the server
         * (LookupResult::senseCard): the note, and the back when the meaning is
         * the answer (EN → EN, or no translation found).
         */
        useSense(index) {
            const sense = this.result.senses[index];
            this.touched = true;
            this.senseIndex = index;
            this.description = sense.note ?? '';
            if (sense.back !== null) this.back = sense.back;
        },

        /**
         * The word under a double click, explained in a small pop-up next to
         * it. Found from the click's position, not the selection: the meanings
         * are buttons, whose text browsers do not select.
         */
        peekAt(event) {
            if (event.target.closest('input, select, textarea, [data-no-peek]')) return;

            const word = this.wordAt(event.clientX, event.clientY) || this.wordIn(String(window.getSelection() ?? ''));
            if (!word) return;

            const width = Math.min(288, window.innerWidth - 16);
            const left = Math.max(8, Math.min(event.clientX - 24, window.innerWidth - width - 8));
            // Below the word, or above it near the bottom of the screen.
            this.peekStyle = event.clientY > window.innerHeight - 220
                ? { left: `${left}px`, top: 'auto', bottom: `${window.innerHeight - event.clientY + 14}px` }
                : { left: `${left}px`, top: `${event.clientY + 14}px`, bottom: 'auto' };

            this.explain(word);
        },

        wordAt(x, y) {
            let node = null;
            let offset = 0;

            if (document.caretPositionFromPoint) {
                const position = document.caretPositionFromPoint(x, y);
                node = position?.offsetNode ?? null;
                offset = position?.offset ?? 0;
            } else if (document.caretRangeFromPoint) {
                const range = document.caretRangeFromPoint(x, y);
                node = range?.startContainer ?? null;
                offset = range?.startOffset ?? 0;
            }

            return node?.nodeType === Node.TEXT_NODE ? this.wordIn(node.data, offset) : null;
        },

        /** The word of `text` around `offset` (or its first word): letters, with inner ' - and ZWNJ. */
        wordIn(text, offset = null) {
            for (const match of text.matchAll(/[\p{L}\p{M}][\p{L}\p{M}'’\u200c-]*/gu)) {
                if (offset === null || (match.index <= offset && offset <= match.index + match[0].length)) {
                    return match[0].replace(/['’\u200c-]+$/u, '').slice(0, 100);
                }
            }

            return null;
        },

        /** English is explained in the chosen language; Persian always in English. */
        async explain(word) {
            const id = ++this.peekId;
            const persian = /\p{Script=Arabic}/u.test(word);
            const direction = persian ? 'fa-en' : this.peekTarget === 'en' ? 'en-en' : 'en-fa';
            const [sl, tl] = direction.split('-');

            this.peek = { word, phonetic: null, translations: [], definitions: [], loading: true, error: null };

            // The device, then Google from the browser: the server cannot reach
            // a translator from Iran, so it is only asked for English meanings
            // or when both have nothing.
            const local = await window.MemFlash?.lookUpOnDevice?.(word, direction)
                ?? await window.MemFlash?.translateInBrowser?.(word, direction);
            if (id !== this.peekId || !this.peek) return;

            if (local) {
                Object.assign(this.peek, { phonetic: local.phonetic, translations: local.translations.slice(0, 4), loading: false });
                return;
            }

            try {
                const response = await this.request(`${this.urls.lookup}?${new URLSearchParams({ q: word, sl, tl })}`);
                const data = await response.json().catch(() => ({}));
                if (id !== this.peekId || !this.peek) return;

                if (!response.ok) {
                    this.peek.error = this.errorMessage(response, data);
                    return;
                }

                const translations = (data.translations ?? []).slice(0, 4);
                this.peek.phonetic = data.phonetic ?? null;
                this.peek.translations = translations;
                // English meanings when asked for, or when there is no translation.
                this.peek.definitions = direction === 'en-en' || translations.length === 0
                    ? (data.senses ?? []).slice(0, 3).map((sense) => sense.definition)
                    : [];
            } catch {
                if (id === this.peekId && this.peek) this.peek.error = 'Could not reach the server.';
            } finally {
                if (id === this.peekId && this.peek) this.peek.loading = false;
            }
        },

        closePeek() {
            this.peekId++;
            this.peek = null;
        },

        /** The setting, kept on the account; an open pop-up follows it at once. */
        setPeekTarget(value, save = true) {
            if (value !== 'fa' && value !== 'en') return;
            const changed = this.peekTarget !== value;
            this.peekTarget = value;

            if (save) this.request(this.urls.peekTarget, { method: 'PUT', body: { target: value } }).catch(() => {});
            if (changed && this.peek) this.explain(this.peek.word);
        },

        /** Make the pop-up's word the lookup itself, where it can be saved. */
        openPeek() {
            this.q = this.peek.word;
            this.closePeek();
            this.lookUp();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        listLabel(list) {
            return list.name + (list.is_default_list ? ' (default)' : '');
        },

        listUrl(id) {
            return this.urls.list.replace('__ID__', id);
        },

        async pickList(value) {
            if (value === 'new') {
                this.creatingList = true;
                this.listError = null;
                this.showSelectedList();
                this.$nextTick(() => this.$refs.newListName?.focus());
                return;
            }

            this.listId = value === '' ? null : Number(value);
            // Remembered right away, so the next visit starts on this list. A
            // failure is harmless: adding a word remembers the list as well.
            this.request(this.urls.select, { method: 'PUT', body: { list_id: this.listId } }).catch(() => {});
        },

        cancelNewList() {
            this.creatingList = false;
            this.newListName = '';
            this.listError = null;
        },

        async createList() {
            const name = this.newListName.trim();
            if (!name || this.creating) return;

            this.creating = true;
            this.listError = null;

            try {
                const response = await this.request(this.urls.createList, { method: 'POST', body: { name } });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    this.listError = this.errorMessage(response, data);
                    return;
                }

                this.lists.push(data.list);
                this.listId = data.list.id;
                this.cancelNewList();
                this.showSelectedList();
            } catch {
                this.listError = 'Could not reach the server. Try again when you are online.';
            } finally {
                this.creating = false;
            }
        },

        async addWord() {
            if (this.adding) return;

            this.adding = true;
            this.notice = null;
            this.noticeListId = null;

            try {
                const response = await this.request(this.urls.addWord, {
                    method: 'POST',
                    body: {
                        front: this.cardFront,
                        back: this.cardBack,
                        description: this.description.trim() || null,
                        pronunciation: this.pronunciation,
                        // Exactly the list on screen. Null here would mean "the
                        // list remembered on the server", which can lag behind
                        // a pick made a moment ago.
                        list_id: this.listId ?? 'default',
                    },
                });
                const data = await response.json().catch(() => ({}));

                this.noticeError = !response.ok;

                if (!response.ok) {
                    this.notice = this.errorMessage(response, data);
                    return;
                }

                // The first word saved without a pick creates the default list.
                const known = this.lists.find((list) => list.id === data.list.id);
                if (known) Object.assign(known, data.list);
                else this.lists.unshift(data.list);

                this.listId = data.list.id;
                this.showSelectedList();
                this.notice = data.message;
                this.noticeListId = data.list.id;

                // Answered on this device from now on, with no wait.
                window.MemFlash?.rememberWord?.(this.cardFront, this.cardBack, this.pronunciation);

                // Ready for the next word.
                this.$refs.query.focus();
                this.$refs.query.select();
            } catch {
                this.noticeError = true;
                this.notice = 'Could not reach the server, so the word was not saved.';
            } finally {
                this.adding = false;
            }
        },

        /** The picker shows listId; needed after "+ New list…" was picked. */
        showSelectedList() {
            this.$nextTick(() => {
                if (this.$refs.listSelect) this.$refs.listSelect.value = this.listId === null ? '' : String(this.listId);
            });
        },

        /**
         * fetch() as JSON. CSRF from Laravel's XSRF-TOKEN cookie, which every
         * response refreshes, rather than the token rendered into the page: a
         * dashboard served from the service worker's cache carries a stale one.
         */
        request(url, { method = 'GET', body } = {}) {
            const cookie = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
            const csrf = cookie
                ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) }
                : { 'X-CSRF-TOKEN': this.csrf };

            return fetch(url, {
                method,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...csrf,
                },
                body: body === undefined ? undefined : JSON.stringify(body),
            });
        },

        errorMessage(response, data) {
            if (data.errors) return Object.values(data.errors).flat()[0];
            if (response.status === 403) return 'That list is not yours.';
            if (response.status === 419) return 'Your session has expired. Reload the page and try again.';
            if (response.status === 429) return 'Too many lookups in a row. Wait a minute and try again.';

            return data.message || 'Something went wrong. Please try again.';
        },
    };
}
</script>
