<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Services\DeckFileProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DeckController extends Controller
{
    public function __construct(
        private readonly DeckFileProcessor $fileProcessor
    ) {}

    /**
     * Show the form for creating a new deck
     */
    public function create(): View
    {
        return view('decks.create');
    }

    /**
     * Store a newly created deck with cards from uploaded file
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'new_cards_per_day' => 'required|integer|min:1|max:100',
            'is_public' => 'required|boolean',
        ], [
            'name.required' => 'Deck name is required.',
            'name.max' => 'Deck name must not exceed 255 characters.',
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'File must be a CSV or Excel file (.csv, .xlsx, .xls).',
            'file.max' => 'File size must be less than 10MB.',
            'new_cards_per_day.required' => 'Cards per day is required.',
            'new_cards_per_day.integer' => 'Cards per day must be a number.',
            'new_cards_per_day.min' => 'Cards per day must be at least 1.',
            'new_cards_per_day.max' => 'Cards per day must not exceed 100.',
            'is_public.required' => 'Please select deck visibility.',
        ]);

        try {
            $file = $request->file('file');

            // Additional file validation
            $validationErrors = $this->fileProcessor->validateFile($file);
            if (! empty($validationErrors)) {
                return back()->withErrors(['file' => implode(' ', $validationErrors)])->withInput();
            }

            // Process the file and create deck with cards
            $deck = $this->fileProcessor->processFile($file, $request->all());

            return redirect()->route('dashboard')->with(
                'success',
                "Deck '{$deck->name}' created successfully with {$deck->cards()->count()} cards!"
            );

        } catch (\InvalidArgumentException $e) {
            Log::warning('Deck creation validation error: ' . $e->getMessage());

            return back()->withErrors(['file' => $e->getMessage()])->withInput();

        } catch (\RuntimeException $e) {
            Log::error('Deck creation file processing error: ' . $e->getMessage());

            return back()->withErrors(['file' => 'File processing failed. Please check your file format and try again.'])->withInput();

        } catch (\Exception $e) {
            Log::error('Deck creation unexpected error: ' . $e->getMessage());

            return back()->withErrors(['file' => 'An unexpected error occurred. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified deck
     */
    public function show(Deck $deck): View
    {
        $this->authorize('view', $deck);

        $deck->load('cards');

        return view('decks.show', compact('deck'));
    }

    /**
     * Show the form for editing the specified deck
     */
    public function edit(Deck $deck): View
    {
        $this->authorize('update', $deck);

        return view('decks.edit', compact('deck'));
    }

    /**
     * Update the specified deck
     */
    public function update(Request $request, Deck $deck): RedirectResponse
    {
        $this->authorize('update', $deck);

        $request->validate([
            'name' => 'required|string|max:255',
            'new_cards_per_day' => 'required|integer|min:1|max:100',
            'is_public' => 'required|boolean',
        ]);

        $deck->update($request->only(['name', 'new_cards_per_day', 'is_public']));

        return redirect()->route('dashboard')->with('success', 'Deck updated successfully!');
    }

    /**
     * Remove the specified deck
     */
    public function destroy(Deck $deck): RedirectResponse
    {
        $this->authorize('delete', $deck);

        $deckName = $deck->name;
        $deck->delete();

        return redirect()->route('dashboard')->with('success', "Deck '{$deckName}' deleted successfully!");
    }
}
