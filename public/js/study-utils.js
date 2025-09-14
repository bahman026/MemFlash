/**
 * Study Utilities JavaScript
 * Shared utilities for study functionality
 */

/**
 * Text-to-speech functionality
 * @param {string} text - Text to speak
 * @param {Object} options - Speech options
 */
function speakText(text, options = {}) {
    if ('speechSynthesis' in window) {
        // Stop any current speech
        speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);

        // Configure speech settings
        utterance.lang = options.lang || 'en-US';
        utterance.rate = options.rate || 0.8;
        utterance.pitch = options.pitch || 1.0;
        utterance.volume = options.volume || 0.8;

        // Visual feedback - show speaking state
        const speakBtn = document.getElementById('speak-btn');
        const fullscreenSpeakBtn = document.getElementById('fullscreen-speak-btn');
        
        if (speakBtn && fullscreenSpeakBtn) {
            const originalContent = speakBtn.innerHTML;
            const fullscreenOriginalContent = fullscreenSpeakBtn.innerHTML;

            const playingContent = `
                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                </svg>
                <span class="hidden sm:inline">Playing...</span>
            `;

            speakBtn.innerHTML = playingContent;
            fullscreenSpeakBtn.innerHTML = `
                <svg class="w-4 h-4 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                </svg>
                Playing...
            `;
            
            speakBtn.classList.add('bg-blue-200');
            fullscreenSpeakBtn.classList.add('bg-blue-200');
            speakBtn.disabled = true;
            fullscreenSpeakBtn.disabled = true;

            // Restore button when speech ends
            utterance.onend = () => {
                speakBtn.innerHTML = originalContent;
                fullscreenSpeakBtn.innerHTML = fullscreenOriginalContent;
                speakBtn.classList.remove('bg-blue-200');
                fullscreenSpeakBtn.classList.remove('bg-blue-200');
                speakBtn.disabled = false;
                fullscreenSpeakBtn.disabled = false;
            };
        }

        // Speak the text
        speechSynthesis.speak(utterance);

        console.log('Speaking text:', text);
    } else {
        console.log('Text-to-speech not supported in this browser');
    }
}

/**
 * Fullscreen modal functionality
 */
const FullscreenModal = {
    open() {
        const modal = document.getElementById('fullscreen-modal');
        const questionText = document.getElementById('card-front').textContent;
        const answerText = document.getElementById('card-back-text').textContent;
        const isAnswerShown = !document.getElementById('card-back').classList.contains('hidden');
        
        // Update fullscreen content
        document.getElementById('fullscreen-question-text').textContent = questionText;
        document.getElementById('fullscreen-answer-text').textContent = answerText;
        
        // Show/hide sections based on current state
        const fullscreenAnswerSection = document.getElementById('fullscreen-answer-section');
        const fullscreenShowAnswerSection = document.getElementById('fullscreen-show-answer-section');
        
        if (isAnswerShown) {
            fullscreenAnswerSection.classList.remove('hidden');
            fullscreenShowAnswerSection.classList.add('hidden');
        } else {
            fullscreenAnswerSection.classList.add('hidden');
            fullscreenShowAnswerSection.classList.remove('hidden');
        }
        
        // Show modal with animation
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add entrance animation
        setTimeout(() => {
            modal.querySelector('.relative').classList.add('animate-in', 'zoom-in-95', 'duration-300');
        }, 10);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    },

    close() {
        const modal = document.getElementById('fullscreen-modal');
        
        // Add exit animation
        modal.querySelector('.relative').classList.add('animate-out', 'zoom-out-95', 'duration-200');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.querySelector('.relative').classList.remove('animate-in', 'zoom-in-95', 'animate-out', 'zoom-out-95');
        }, 200);
        
        // Restore body scroll
        document.body.style.overflow = '';
    },

    updateContent() {
        const modal = document.getElementById('fullscreen-modal');
        
        // Only update if fullscreen is open
        if (!modal.classList.contains('hidden')) {
            const questionText = document.getElementById('card-front').textContent;
            const answerText = document.getElementById('card-back-text').textContent;
            const isAnswerShown = !document.getElementById('card-back').classList.contains('hidden');
            
            // Update fullscreen content
            document.getElementById('fullscreen-question-text').textContent = questionText;
            document.getElementById('fullscreen-answer-text').textContent = answerText;
            
            // Show/hide sections based on current state
            const fullscreenAnswerSection = document.getElementById('fullscreen-answer-section');
            const fullscreenShowAnswerSection = document.getElementById('fullscreen-show-answer-section');
            
            if (isAnswerShown) {
                fullscreenAnswerSection.classList.remove('hidden');
                fullscreenShowAnswerSection.classList.add('hidden');
            } else {
                fullscreenAnswerSection.classList.add('hidden');
                fullscreenShowAnswerSection.classList.remove('hidden');
            }
        }
    },

    showAnswer() {
        // Show the answer in fullscreen
        const fullscreenAnswerSection = document.getElementById('fullscreen-answer-section');
        const fullscreenShowAnswerSection = document.getElementById('fullscreen-show-answer-section');
        
        fullscreenAnswerSection.classList.remove('hidden');
        fullscreenShowAnswerSection.classList.add('hidden');
    }
};

/**
 * Progress update functionality
 * @param {Object} data - Progress data
 */
function updateProgress(data) {
    const {
        totalCards = 0,
        remainingCards = 0,
        completedCards = 0
    } = data;

    const progressPercentage = totalCards > 0 ? (completedCards / totalCards) * 100 : 0;

    // Update progress bar
    const progressBar = document.getElementById('progress-bar');
    if (progressBar) {
        progressBar.style.width = `${progressPercentage}%`;
    }

    // Update progress text
    const progressText = document.getElementById('progress-text');
    if (progressText) {
        progressText.textContent = `${completedCards} / ${totalCards}`;
    }

    // Update progress percentage
    const progressPercentageEl = document.getElementById('progress-percentage');
    if (progressPercentageEl) {
        progressPercentageEl.textContent = `${Math.round(progressPercentage)}%`;
    }

    // Update stats
    const cardsRemaining = document.getElementById('cards-remaining');
    const completedCardsEl = document.getElementById('completed-cards');
    const sessionStats = document.getElementById('session-stats');
    const cardsLoaded = document.getElementById('cards-loaded');

    if (cardsRemaining) cardsRemaining.textContent = remainingCards;
    if (completedCardsEl) completedCardsEl.textContent = completedCards;
    if (sessionStats) sessionStats.textContent = `${completedCards} completed`;
    if (cardsLoaded) cardsLoaded.textContent = totalCards;

    // Update mobile stats cards
    const cardsRemainingMobile = document.getElementById('cards-remaining-mobile');
    const completedCardsMobile = document.getElementById('completed-cards-mobile');

    if (cardsRemainingMobile) cardsRemainingMobile.textContent = remainingCards;
    if (completedCardsMobile) completedCardsMobile.textContent = completedCards;
}

/**
 * Card display functionality
 * @param {Object} card - Card data
 */
function displayCard(card) {
    if (!card || !card.front || !card.back) {
        console.error('Invalid card data:', card);
        return false;
    }

    // Update card content
    const cardFront = document.getElementById('card-front');
    const cardBackText = document.getElementById('card-back-text');

    if (cardFront) cardFront.textContent = card.front;
    if (cardBackText) cardBackText.textContent = card.back;

    // Hide answer and rating buttons
    const cardBack = document.getElementById('card-back');
    const ratingButtons = document.getElementById('rating-buttons');
    const showAnswerSection = document.getElementById('show-answer-section');

    if (cardBack) cardBack.classList.add('hidden');
    if (ratingButtons) ratingButtons.classList.add('hidden');
    if (showAnswerSection) showAnswerSection.classList.remove('hidden');

    return true;
}

/**
 * Show answer functionality
 */
function showAnswer() {
    const cardBack = document.getElementById('card-back');
    const ratingButtons = document.getElementById('rating-buttons');
    const showAnswerSection = document.getElementById('show-answer-section');

    if (cardBack) cardBack.classList.remove('hidden');
    if (ratingButtons) ratingButtons.classList.remove('hidden');
    if (showAnswerSection) showAnswerSection.classList.add('hidden');
}

/**
 * Session state management
 */
const SessionState = {
    showLoading() {
        const loadingState = document.getElementById('loading-state');
        const cardContent = document.getElementById('card-content');
        const sessionComplete = document.getElementById('session-complete');
        const errorState = document.getElementById('error-state');

        if (loadingState) loadingState.classList.remove('hidden');
        if (cardContent) cardContent.classList.add('hidden');
        if (sessionComplete) sessionComplete.classList.add('hidden');
        if (errorState) errorState.classList.add('hidden');
    },

    showCard() {
        const loadingState = document.getElementById('loading-state');
        const cardContent = document.getElementById('card-content');
        const sessionComplete = document.getElementById('session-complete');
        const errorState = document.getElementById('error-state');

        if (loadingState) loadingState.classList.add('hidden');
        if (cardContent) cardContent.classList.remove('hidden');
        if (sessionComplete) sessionComplete.classList.add('hidden');
        if (errorState) errorState.classList.add('hidden');
    },

    showComplete() {
        const loadingState = document.getElementById('loading-state');
        const cardContent = document.getElementById('card-content');
        const sessionComplete = document.getElementById('session-complete');
        const errorState = document.getElementById('error-state');

        if (loadingState) loadingState.classList.add('hidden');
        if (cardContent) cardContent.classList.add('hidden');
        if (sessionComplete) sessionComplete.classList.remove('hidden');
        if (errorState) errorState.classList.add('hidden');
    },

    showError(message) {
        const loadingState = document.getElementById('loading-state');
        const cardContent = document.getElementById('card-content');
        const sessionComplete = document.getElementById('session-complete');
        const errorState = document.getElementById('error-state');
        const errorMessage = document.getElementById('error-message');

        if (loadingState) loadingState.classList.add('hidden');
        if (cardContent) cardContent.classList.add('hidden');
        if (sessionComplete) sessionComplete.classList.add('hidden');
        if (errorState) errorState.classList.remove('hidden');
        if (errorMessage) errorMessage.textContent = message;
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        speakText,
        FullscreenModal,
        updateProgress,
        displayCard,
        showAnswer,
        SessionState
    };
}
