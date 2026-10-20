<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\GoogleTranslateSync;
use App\Services\GoogleTranslate\SavedWordsSync;
use App\Services\GoogleTranslate\SheetLink;
use App\Services\GoogleTranslate\SheetUnavailable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The dashboard's "Google Translate" section: link the sheet that Google
 * Translate's saved words are exported to, and sync it into a word list.
 *
 * Plain forms that redirect back with a flash message, so the section needs no
 * JavaScript. The list is authorized like a saved word: DeckPolicy's `update`.
 */
class GoogleTranslateSyncController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly SavedWordsSync $syncer) {}

    /**
     * PUT /word-lists/google-translate
     *
     * Links a sheet (or changes the link or the list) and syncs straight away.
     * The link is kept even when that first sync fails, so fixing the sheet's
     * sharing and pressing "Sync now" is enough.
     */
    public function connect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sheet_url' => 'required|string|max:2000',
            'list_id' => ['nullable', 'regex:/^(default|[1-9][0-9]*)$/'],
        ]);

        if (SheetLink::parse($validated['sheet_url']) === null) {
            throw ValidationException::withMessages([
                'sheet_url' => 'Paste the link of a Google Sheet: https://docs.google.com/spreadsheets/d/…',
            ]);
        }

        $list = match ($validated['list_id'] ?? 'default') {
            'default' => null,
            default => Deck::query()->findOrFail((int) $validated['list_id']),
        };

        if ($list !== null) {
            $this->authorize('update', $list);
        }

        $user = $request->user();
        $sync = $user->googleTranslateSync()->firstOrNew();

        // Another sheet or another list starts the memory of imported words
        // over; words the list already has are still not added twice.
        if ($sync->sheet_url !== trim($validated['sheet_url']) || $sync->deck_id !== $list?->id) {
            $sync->imported_words = [];
        }

        $sync->fill(['sheet_url' => trim($validated['sheet_url']), 'deck_id' => $list?->id, 'last_error' => null])->save();

        return $this->run($sync);
    }

    /**
     * POST /word-lists/google-translate/sync
     */
    public function sync(Request $request): RedirectResponse
    {
        $sync = $request->user()->googleTranslateSync()->firstOrFail();

        return $this->run($sync);
    }

    /**
     * DELETE /word-lists/google-translate
     *
     * Forgets the link. The words already brought in stay in their list.
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->googleTranslateSync()->delete();

        return back()->with('success', 'Google Translate is no longer synced. The words already brought in stay in their list.');
    }

    private function run(GoogleTranslateSync $sync): RedirectResponse
    {
        try {
            return back()->with('success', $this->syncer->sync($sync)->message());
        } catch (SheetUnavailable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
