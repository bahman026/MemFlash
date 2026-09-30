/**
 * Unified Study Session JavaScript
 * Handles study sessions for both user-created and static (curriculum) decks
 */

// Load utilities
if (typeof FullscreenModal === 'undefined' || typeof speakText === 'undefined') {
    // Load utilities if not already loaded
    const script = document.createElement('script');
    script.src = '/js/study-utils.js';
    document.head.appendChild(script);
}

// A card still in (re)learning that is due again within this many seconds comes
// back later in the same session, as Anki's learning queue does.
const LEARN_AHEAD_SECONDS = 20 * 60;

// Identifies one rating, so a save retried after a lost response is recognised by
// the server instead of being applied twice.
function newReviewUuid() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();

    const bytes = window.crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('');

    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

class StudySession {
    constructor(config = {}) {
        this.cards = [];
        this.currentCardIndex = 0;
        this.deckInfo = null;
        this.isAnswerShown = false;
        this.pendingUpdates = [];
        this.totalCards = 0;
        this.isRating = false;
        this.autoPronunciationTimeout = null; // Track auto-pronunciation timeout
        this.userHasInteracted = false; // Track if user has interacted with audio
        this.isFirstCard = true; // Track if this is the first card shown
        
        // Configuration
        this.config = {
            type: 'user', // 'user' or 'static'
            apiEndpoint: '/api/study/batch-update',
            autoPronunciation: true, // Enable auto-pronunciation by default
            autoPronunciationDelay: 1000, // 1 second delay
            ...config
        };

        // Attached once, before loading. Attaching them in init() meant a failed
        // first load left Try Again dead, and a retried init() attached them twice
        // so every click rated two cards.
        this.setupEventListeners();
        this.init();
    }

    async init() {
        try {
            console.log(`Initializing ${this.config.type} study session...`);
            
            if (this.config.type === 'static') {
                // Static decks: cards are pre-loaded from server
                this.cards = window.studyConfig?.cards || [];
                this.totalCards = this.cards.length;
                console.log('Cards loaded:', this.cards.length);
                console.log('Cards data:', this.cards);
                
                if (this.cards.length === 0) {
                    console.log('No cards available, showing session complete');
                    this.showSessionComplete();
                    return;
                }
            } else {
                // User decks: load cards from API
                await this.loadCards();
            }

            this.showCurrentCard();
            console.log('Study session initialized successfully');
        } catch (error) {
            console.error('Failed to initialize study session:', error);
            console.error('Error details:', error.message, error.stack);
            this.showError('Failed to load study session. Please try again.');
        }
    }

    async loadCards() {
        try {
            const deckId = window.studyConfig?.deckId;
            if (!deckId) {
                throw new Error('Deck ID not found in configuration');
            }

            console.log('Fetching cards for deck', deckId, '...');
            const response = await fetch(`/api/study/${deckId}/cards`);

            console.log('Response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error:', errorText);
                throw new Error(`Failed to load cards: ${response.status} - ${errorText}`);
            }

            const data = await response.json();
            console.log('API Response:', data);

            this.cards = data.cards;
            this.deckInfo = data.deck;
            this.totalCards = this.cards.length;

            console.log('Loaded cards:', this.cards.length);

            if (this.cards.length === 0) {
                console.log('No cards available for this deck');
            }
        } catch (error) {
            console.error('Error in loadCards:', error);
            throw error;
        }
    }

    setupEventListeners() {
        document.getElementById('show-answer-btn').addEventListener('click', () => this.showAnswer());
        document.getElementById('speak-btn').addEventListener('click', () => this.playAudio());
        document.getElementById('fullscreen-btn').addEventListener('click', () => this.openFullscreen());
        document.getElementById('exit-fullscreen-btn').addEventListener('click', () => this.closeFullscreen());
        document.getElementById('fullscreen-speak-btn').addEventListener('click', () => this.playAudio());
        document.getElementById('fullscreen-show-answer-btn').addEventListener('click', () => this.showFullscreenAnswer());
        document.getElementById('again-btn').addEventListener('click', () => this.rateCard(1));
        document.getElementById('hard-btn').addEventListener('click', () => this.rateCard(2));
        document.getElementById('good-btn').addEventListener('click', () => this.rateCard(3));
        document.getElementById('easy-btn').addEventListener('click', () => this.rateCard(4));
        document.getElementById('fullscreen-again-btn').addEventListener('click', () => this.rateCard(1));
        document.getElementById('fullscreen-hard-btn').addEventListener('click', () => this.rateCard(2));
        document.getElementById('fullscreen-good-btn').addEventListener('click', () => this.rateCard(3));
        document.getElementById('fullscreen-easy-btn').addEventListener('click', () => this.rateCard(4));
        document.getElementById('restart-session').addEventListener('click', () => this.restartSession());
        document.getElementById('retry-btn').addEventListener('click', () => this.retry());

        // Answers are saved as they are given, so this only fires while a save is
        // failing (offline, or the login expired).
        window.addEventListener('beforeunload', (e) => {
            if (this.pendingUpdates.length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Close fullscreen on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeFullscreen();
            }
        });
    }

    showCurrentCard() {
        // Start the clock for review_duration_ms; the server records how long each
        // answer took so the deck list can estimate today's workload in minutes.
        this.cardShownAt = Date.now();

        console.log('showCurrentCard called, cards length:', this.cards.length);
        console.log('Current card index:', this.currentCardIndex);

        // Clear any existing auto-pronunciation timeout
        if (this.autoPronunciationTimeout) {
            clearTimeout(this.autoPronunciationTimeout);
            this.autoPronunciationTimeout = null;
        }

        SessionState.showCard();

        if (this.cards.length === 0) {
            console.log('No cards available, showing session complete');
            this.showSessionComplete();
            return;
        }

        if (this.currentCardIndex >= this.cards.length) {
            console.log('Current card index out of bounds, showing session complete');
            this.showSessionComplete();
            return;
        }

        const card = this.cards[this.currentCardIndex];
        console.log('Showing card:', card);
        console.log('Card front:', card.front);
        console.log('Card back:', card.back);

        if (!displayCard(card)) {
            this.showError('Invalid card data. Please try again.');
            return;
        }

        this.isAnswerShown = false;
        this.updateProgress();
        
        // Auto-pronunciation with delay (only after user interaction)
        if (this.config.autoPronunciation && this.userHasInteracted) {
            this.scheduleAutoPronunciation();
        } else if (this.isFirstCard) {
            // For first card, show a hint to click play button
            this.showAudioHint();
        }
        
        this.isFirstCard = false;
        
        console.log('Card displayed successfully');
    }

    showAnswer() {
        showAnswer();
        this.isAnswerShown = true;
    }

    async rateCard(rating) {
        // One rating at a time: a double tap must not grade the next card unseen.
        if (this.cards.length === 0 || this.isRating) return;
        this.isRating = true;

        try {
            const card = this.cards[this.currentCardIndex];

            this.pendingUpdates.push({
                card_id: card.id,
                rating: rating,
                review_duration_ms: this.cardShownAt ? Date.now() - this.cardShownAt : null,
                client_uuid: newReviewUuid(),
            });

            // Saved now, not at the end of the session. Batching everything until
            // the queue was empty meant leaving early, closing the tab or an expired
            // login lost every answer, and every card was replayed at one instant.
            // A failed save stays queued and goes out with the next one.
            const saved = await this.sendUpdates();

            this.cards.splice(this.currentCardIndex, 1);
            if (this.comesBackThisSession(saved?.get(card.id), rating)) {
                this.cards.push(card);
            }
            if (this.currentCardIndex >= this.cards.length) {
                this.currentCardIndex = 0;
            }

            this.updateProgress();

            if (this.cards.length === 0) {
                await this.showSessionComplete();
                return;
            }

            this.showCurrentCard();
            this.updateFullscreenContent();
        } finally {
            this.isRating = false;
        }
    }

    /**
     * Whether a just-rated card is shown again before the session ends.
     *
     * The server's answer decides: a card still in (re)learning is due again in
     * minutes, so it comes back. One scheduled further out is done for today.
     * Previously only Easy removed a card, so the last rating every card ever got
     * was Easy. With no answer from the server (offline), only a forgotten card
     * is repeated.
     */
    comesBackThisSession(result, rating) {
        if (!result) return rating === 1;

        const learning = result.state === 'learning' || result.state === 'relearning';

        return learning && result.scheduled_seconds <= LEARN_AHEAD_SECONDS;
    }

    /**
     * Send every queued rating. Resolves to a Map of card id => new state, or null
     * when the save failed (the ratings then stay queued).
     */
    async sendUpdates() {
        if (this.pendingUpdates.length === 0) return new Map();

        const batch = [...this.pendingUpdates];

        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Without it a validation failure comes back as a redirect to
                    // an HTML page, which fetch follows and reports as a 200.
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ updates: batch })
            });

            if (!response.ok) {
                throw new Error(`Saving answers failed with status ${response.status}`);
            }

            const data = await response.json();
            this.pendingUpdates = this.pendingUpdates.filter((update) => !batch.includes(update));

            return new Map((data.updated_cards || []).map((result) => [result.id, result]));
        } catch (error) {
            console.error('Failed to send updates:', error);

            return null;
        }
    }

    updateProgress() {
        // Counted from the cards actually loaded. The static screen used its daily
        // limit here, so 3 due cards under a limit of 10 started at "7 / 10".
        const totalCards = this.totalCards;
        const remainingCards = this.cards.length;
        const completedCards = Math.max(0, totalCards - remainingCards);

        updateProgress({
            totalCards,
            remainingCards,
            completedCards
        });
    }

    async showSessionComplete() {
        // Fullscreen left open would strand the last card and its dead buttons.
        this.closeFullscreen();

        if (this.pendingUpdates.length > 0 && !(await this.sendUpdates())) {
            this.showError('Some of your answers could not be saved. Check your connection and try again.');
            return;
        }

        SessionState.showComplete();
    }

    async retry() {
        if (this.pendingUpdates.length > 0) {
            SessionState.showLoading();

            if (await this.sendUpdates()) {
                this.cards.length === 0 ? await this.showSessionComplete() : this.showCurrentCard();
            } else {
                this.showError('Your answers still could not be saved. Check your connection and try again.');
            }
            return;
        }

        // Nothing unsaved: reloading is the cleanest restart from any failure.
        window.location.reload();
    }

    showError(message) {
        SessionState.showError(message);
    }

    async restartSession() {
        SessionState.showLoading();

        try {
            if (this.config.type === 'static') {
                // Static decks: reload the page to get fresh cards
                window.location.reload();
            } else {
                // User decks: reload cards from API
                await this.loadCards();
                this.currentCardIndex = 0;
                this.showCurrentCard();
            }
        } catch (error) {
            console.error('Failed to restart session:', error);
            this.showError('Failed to restart session. Please try again.');
        }
    }

    /**
     * Schedule auto-pronunciation with configured delay
     */
    scheduleAutoPronunciation() {
        // Clear any existing timeout
        if (this.autoPronunciationTimeout) {
            clearTimeout(this.autoPronunciationTimeout);
        }

        // Schedule pronunciation after delay
        this.autoPronunciationTimeout = setTimeout(() => {
            this.playAudio();
            this.autoPronunciationTimeout = null;
        }, this.config.autoPronunciationDelay);
    }

    /**
     * Show audio hint for first card
     */
    showAudioHint() {
        const speakBtn = document.getElementById('speak-btn');
        if (speakBtn) {
            speakBtn.classList.add('animate-pulse', 'ring-2', 'ring-blue-500');
            speakBtn.title = 'Click to enable audio and auto-pronunciation';
            
            // Remove hint after 5 seconds
            setTimeout(() => {
                speakBtn.classList.remove('animate-pulse', 'ring-2', 'ring-blue-500');
                speakBtn.title = 'Play question audio';
            }, 5000);
        }
    }

    /**
     * Cancel auto-pronunciation if user manually triggers audio
     */
    playAudio() {
        // Cancel auto-pronunciation if it's scheduled
        if (this.autoPronunciationTimeout) {
            clearTimeout(this.autoPronunciationTimeout);
            this.autoPronunciationTimeout = null;
        }

        // Mark that user has interacted with audio (enables auto-play for future cards)
        if (!this.userHasInteracted) {
            this.userHasInteracted = true;
            console.log('User interaction recorded - auto-pronunciation enabled for future cards');
        }

        const frontText = document.getElementById('card-front').textContent;
        speakText(frontText);
    }

    openFullscreen() {
        FullscreenModal.open();
    }

    closeFullscreen() {
        FullscreenModal.close();
    }

    showFullscreenAnswer() {
        FullscreenModal.showAnswer();
        this.showAnswer();
    }

    updateFullscreenContent() {
        FullscreenModal.updateContent();
    }
}

// Factory functions for different session types
function createUserStudySession() {
    return new StudySession({
        type: 'user',
        apiEndpoint: '/api/study/batch-update'
    });
}

function createStaticStudySession() {
    return new StudySession({
        type: 'static',
        apiEndpoint: '/api/static-study/batch-update'
    });
}

// Auto-initialize based on configuration
document.addEventListener('DOMContentLoaded', () => {
    const config = window.studyConfig || {};
    
    if (config.cards && config.totalCards) {
        // Static deck session
        createStaticStudySession();
    } else if (config.deckId) {
        // User deck session
        createUserStudySession();
    } else {
        console.error('Invalid study configuration:', config);
    }
});
