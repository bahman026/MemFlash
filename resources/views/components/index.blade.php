{{-- 
    Component Index - This file serves as a reference for all available components
    This file is not meant to be rendered, but serves as documentation
--}}

{{-- 
    UI COMPONENTS
    =============
--}}

{{-- Buttons --}}
{{-- <x-ui.buttons.action-button href="/path" title="Action" /> --}}
{{-- <x-ui.buttons.back-to-dashboard /> --}}
{{-- <x-ui.buttons.edit-deck :deck="$deck" /> --}}

{{-- Cards --}}
{{-- <x-ui.cards.deck-card :deck="$deck" type="user|static" /> --}}
{{-- <x-ui.cards.personal-deck-card :deck="$deck" /> --}}
{{-- <x-ui.cards.static-deck-card :static-deck="$staticDeck" /> --}}

{{-- Forms --}}
{{-- <x-ui.forms.input name="field" label="Label" required /> --}}
{{-- <x-ui.forms.textarea name="field" label="Label" rows="3" /> --}}

{{-- Modals --}}
{{-- <x-ui.modals.deck-create-modal /> --}}

{{-- Navigation --}}
{{-- <x-ui.navigation.profile-dropdown :user="auth()->user()" /> --}}

{{-- Feedback --}}
{{-- <x-ui.feedback.alert type="success|error|warning|info" dismissible>Message</x-ui.feedback.alert> --}}
{{-- <x-ui.feedback.static-deck-limits :user="$user" /> --}}
{{-- <x-ui.feedback.dashboard-stats :decks="$decks" :total-cards="$totalCards" :total-static-cards="$totalStaticCards" /> --}}
{{-- <x-ui.feedback.session-messages /> --}}

{{-- 
    LAYOUT COMPONENTS
    =================
--}}

{{-- Main Layouts --}}
{{-- <x-layouts.app :header-title="'Title'" :hide-footer="false">Content</x-layouts.app> --}}
{{-- <x-layouts.deck :deck="$deck">Content</x-layouts.deck> --}}
{{-- <x-layouts.static-deck :static-deck="$staticDeck">Content</x-layouts.static-deck> --}}
{{-- <x-layouts.dashboard>Content</x-layouts.dashboard> --}}
{{-- <x-layouts.minimal>Content</x-layouts.minimal> --}}

{{-- Layout Sections --}}
{{-- <x-layouts.sections.header :title="'Title'" :subtitle="'Subtitle'" /> --}}
{{-- <x-layouts.sections.footer /> --}}
{{-- <x-layouts.sections.simple-footer /> --}}
{{-- <x-layouts.sections.welcome-section :user="$user" /> --}}
{{-- <x-layouts.sections.static-decks-section :static-decks="$staticDecks" :user="$user" /> --}}
{{-- <x-layouts.sections.personal-decks-section :decks="$decks" :user="$user" /> --}}

{{-- Layout Partials --}}
{{-- <x-layouts.partials.header-actions>Actions</x-layouts.partials.header-actions> --}}

{{-- 
    STUDY COMPONENTS
    ================
--}}

{{-- Study Components --}}
{{-- <x-study.progress-bar :total-cards="10" :completed-cards="5" :remaining-cards="5" /> --}}
{{-- <x-study.stats-cards :remaining-cards="5" :completed-cards="5" :show-mobile="true" /> --}}
{{-- <x-study.study-tips /> --}}
{{-- <x-study.session-info :deck-name="'My Deck'" :cards-loaded="10" /> --}}
{{-- <x-study.study-card /> --}}
{{-- <x-study.study-header :title="'Study Session'" :subtitle="'Study Session'" :remaining-cards="5" :completed-cards="5" /> --}}
{{-- <x-study.fullscreen-modal /> --}}

{{-- 
    JAVASCRIPT FILES
    ================
--}}

{{-- JavaScript Files --}}
{{-- study-utils.js - Shared utilities for study functionality --}}
{{-- study-session-unified.js - Unified study session for both user and static decks --}}
