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
        'defaultListName' => \App\Services\WordListService::DEFAULT_LIST_NAME,
        'csrf' => csrf_token(),
        'urls' => [
            'lookup' => route('words.lookup'),
            'createList' => route('word-lists.store'),
            'select' => route('word-lists.select'),
            'addWord' => route('word-lists.add-word'),
            'list' => route('decks.show', ['deck' => '__ID__']),
        ],
    ];
@endphp

<section x-data="wordLookup(@js($config))" class="bg-white shadow-sm rounded-xl border border-gray-100" aria-labelledby="word-lookup-title">
    <div class="p-3 sm:p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="word-lookup-title" class="text-base sm:text-lg font-bold text-gray-900">Look up a word</h2>

            <!-- Direction, as Google Translate's sl/tl -->
            <div class="flex flex-wrap gap-1" role="radiogroup" aria-label="Translation direction">
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

        /** Alpine runs this on mount. */
        init() {
            if (this.q.trim()) this.lookUp();
            else if (this.autofocus) this.$nextTick(() => this.$refs.query.focus());
        },

        get cardFront() {
            return (this.missing === 'front' ? this.typed : this.front).trim();
        },

        get cardBack() {
            return (this.missing === 'back' ? this.typed : this.back).trim();
        },

        async lookUp() {
            const q = this.q.trim();
            if (!q || this.loading) return;

            this.loading = true;
            this.error = null;
            this.syncAddressBar();

            try {
                const response = await this.request(`${this.urls.lookup}?${this.directionParams(q)}`);
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    this.error = this.errorMessage(response, data);
                    return;
                }

                this.result = data;
                this.senseIndex = 0;
                this.front = data.card.front;
                this.back = data.card.back;
                this.description = data.card.description ?? '';
                this.pronunciation = data.card.pronunciation;
                this.missing = !data.card.front ? 'front' : !data.card.back ? 'back' : null;
                this.typed = '';
                this.notice = null;
            } catch {
                this.error = 'Could not reach the server. Looking words up needs a connection.';
            } finally {
                this.loading = false;
            }
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
            this.senseIndex = index;
            this.description = sense.note ?? '';
            if (sense.back !== null) this.back = sense.back;
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
