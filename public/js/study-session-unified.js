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

class StudySession {
    constructor(config = {}) {
        this.cards = [];
        this.currentCardIndex = 0;
        this.deckInfo = null;
        this.isAnswerShown = false;
        this.pendingUpdates = [];
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

        this.init();
    }

    async init() {
        try {
            console.log(`Initializing ${this.config.type} study session...`);
            
            if (this.config.type === 'static') {
                // Static decks: cards are pre-loaded from server
                this.cards = window.studyConfig?.cards || [];
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
                console.log('Cards loaded, setting up event listeners...');
            }
            
            this.setupEventListeners();
            console.log('Event listeners set up, showing current card...');
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
        document.getElementById('again-btn').addEventListener('click', () => this.rateCard(0));
        document.getElementById('hard-btn').addEventListener('click', () => this.rateCard(1));
        document.getElementById('good-btn').addEventListener('click', () => this.rateCard(2));
        document.getElementById('easy-btn').addEventListener('click', () => this.rateCard(3));
        document.getElementById('fullscreen-again-btn').addEventListener('click', () => this.rateCard(0));
        document.getElementById('fullscreen-hard-btn').addEventListener('click', () => this.rateCard(1));
        document.getElementById('fullscreen-good-btn').addEventListener('click', () => this.rateCard(2));
        document.getElementById('fullscreen-easy-btn').addEventListener('click', () => this.rateCard(3));
        document.getElementById('restart-session').addEventListener('click', () => this.restartSession());
        document.getElementById('retry-btn').addEventListener('click', () => this.init());
        
        // Close fullscreen on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeFullscreen();
            }
        });
    }

    showCurrentCard() {
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

    async rateCard(quality) {
        if (this.cards.length === 0) return;

        const card = this.cards[this.currentCardIndex];

        // Add to pending updates
        this.pendingUpdates.push({
            card_id: card.id,
            quality: quality
        });

        // Handle card based on quality according to the algorithm
        if (quality === 3) {
            // Easy - remove from array completely
            this.cards.splice(this.currentCardIndex, 1);
        } else {
            // Again, Hard, Good - move to end of array for review
            const movedCard = this.cards.splice(this.currentCardIndex, 1)[0];
            this.cards.push(movedCard);
        }

        // Update progress
        this.updateProgress();

        // Check if session is complete
        if (this.shouldEndSession()) {
            await this.sendUpdates();
            this.showSessionComplete();
            return;
        }

        // Show next card
        this.showCurrentCard();
        
        // Update fullscreen if it's open
        this.updateFullscreenContent();
    }

    shouldEndSession() {
        if (this.cards.length === 0) {
            return true;
        }

        if (this.config.type === 'static') {
            // Static decks: check daily limit
            const totalCards = window.studyConfig?.totalCards || 0;
            const completedCards = totalCards - this.cards.length;
            return completedCards >= totalCards;
        }

        // User decks: end when all cards are completed
        return false;
    }

    async sendUpdates() {
        if (this.pendingUpdates.length === 0) return;

        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    updates: this.pendingUpdates
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update cards');
            }

            console.log('Cards updated successfully');
            this.pendingUpdates = [];
        } catch (error) {
            console.error('Failed to send updates:', error);
        }
    }

    updateProgress() {
        let totalCards, remainingCards, completedCards;

        if (this.config.type === 'static') {
            totalCards = window.studyConfig?.totalCards || 0;
            remainingCards = this.cards.length;
            completedCards = totalCards > 0 ? totalCards - remainingCards : 0;
        } else {
            totalCards = this.deckInfo?.cards_loaded || 0;
            remainingCards = this.cards.length;
            completedCards = totalCards - remainingCards;
        }

        updateProgress({
            totalCards,
            remainingCards,
            completedCards
        });
    }

    showSessionComplete() {
        SessionState.showComplete();
        this.sendUpdates();
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
                this.pendingUpdates = [];
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
