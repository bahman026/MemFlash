@props([
    // The user's App\Models\GoogleTranslateSync, with `deck` loaded, or null.
    'sync' => null,
    // WordListService::payload() of every deck the user owns: any deck takes words.
    'lists' => [],
])

@php
    $defaultList = collect($lists)->firstWhere('is_default_list', true);
    // deck_id null = the default list, which may not exist yet.
    $selected = old('list_id', $sync?->deck_id ?? 'default');
    $listName = $sync?->deck?->name ?? ($defaultList['name'] ?? \App\Services\WordListService::DEFAULT_LIST_NAME);
    $listId = $sync?->deck_id ?? ($defaultList['id'] ?? null);
@endphp

{{-- Plain forms, no JavaScript: see GoogleTranslateSyncController. --}}
<section id="google-translate" class="bg-white shadow-sm rounded-xl border border-gray-100" aria-labelledby="google-translate-title">
    <div class="p-3 sm:p-4 space-y-3">
        <div>
            <h2 id="google-translate-title" class="text-base sm:text-lg font-bold text-gray-900">Words saved in Google Translate</h2>
            <p class="text-sm text-gray-500">
                @if($sync)
                    Synced into @if($listId)<a href="{{ route('decks.show', $listId) }}" class="font-medium text-primary-700 hover:underline">{{ $listName }}</a>@else<span class="font-medium">{{ $listName }}</span>@endif
                    every day, and whenever you press Sync now. New words only; nothing here is ever deleted.
                @else
                    Bring the words you starred in Google Translate into a word list, and keep them coming.
                @endif
            </p>
        </div>

        @if($sync)
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <span class="text-gray-600">
                    @if($sync->last_synced_at)
                        Last synced {{ $sync->last_synced_at->diffForHumans() }}
                        · {{ $sync->last_added }} new {{ \Illuminate\Support\Str::plural('word', $sync->last_added) }}
                        · {{ count($sync->imported_words) }} in all
                    @else
                        Not synced yet
                    @endif
                </span>

                <form method="POST" action="{{ route('google-translate.sync') }}">
                    @csrf
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">Sync now</button>
                </form>

                <a href="{{ $sync->sheet_url }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-800 underline">Open the sheet</a>

                <form method="POST" action="{{ route('google-translate.disconnect') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-500 hover:text-red-700 underline">Stop syncing</button>
                </form>
            </div>

            @if($sync->last_error)
                <p class="text-sm text-red-600" role="alert">{{ $sync->last_error }}</p>
            @endif
        @endif

        <details @if(! $sync || $errors->has('sheet_url') || $errors->has('list_id')) open @endif class="group">
            <summary class="cursor-pointer text-sm font-medium text-primary-700 hover:text-primary-800">
                {{ $sync ? 'Change the sheet or the list' : 'Set it up' }}
            </summary>

            <ol class="mt-2 ml-5 list-decimal space-y-1 text-sm text-gray-600">
                <li>Open <a href="https://translate.google.com/saved" target="_blank" rel="noopener noreferrer" class="text-primary-700 underline">Google Translate → Saved</a> on a computer.</li>
                <li>Open the ⋮ menu and choose <strong>Export to Google Sheets</strong>.</li>
                <li>In that sheet, <strong>Share</strong> → General access → <strong>Anyone with the link</strong> (Viewer).</li>
                <li>Copy the sheet's link and paste it below. Export again whenever you have saved new words; the link stays the same if you export into the same sheet.</li>
            </ol>

            <form method="POST" action="{{ route('google-translate.connect') }}" class="mt-3 space-y-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="google-translate-sheet" class="block text-sm font-medium text-gray-700">Google Sheet link</label>
                    <input id="google-translate-sheet" name="sheet_url" type="url" required maxlength="2000" autocomplete="off"
                           value="{{ old('sheet_url', $sync?->sheet_url) }}"
                           placeholder="https://docs.google.com/spreadsheets/d/…"
                           class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('sheet_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[12rem]">
                        <label for="google-translate-list" class="block text-sm font-medium text-gray-700">Into the list</label>
                        <select id="google-translate-list" name="list_id"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-base bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @unless($defaultList)
                                <option value="default" @selected((string) $selected === 'default')>{{ \App\Services\WordListService::DEFAULT_LIST_NAME }} (new)</option>
                            @endunless
                            @foreach($lists as $list)
                                <option value="{{ $list['is_default_list'] ? 'default' : $list['id'] }}"
                                        @selected($list['is_default_list'] ? ((string) $selected === 'default' || (string) $selected === (string) $list['id']) : (string) $selected === (string) $list['id'])>
                                    {{ $list['name'] }} ({{ $list['cards_count'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('list_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        {{ $sync ? 'Save and sync' : 'Link and sync' }}
                    </button>
                </div>
            </form>
        </details>
    </div>
</section>
