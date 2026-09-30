<x-legal.page title="Terms of Service" updated="30 September 2026">
    <p>
        By signing in to MemFlash you agree to these terms. If you do not agree, please do not use the app.
    </p>

    <h2>The service</h2>
    <p>
        MemFlash is a free flashcard app for learning vocabulary with spaced repetition. It is provided as it is,
        without guarantees of availability, accuracy of the included vocabulary, or fitness for any particular
        purpose. We may change or stop features at any time.
    </p>

    <h2>Your account</h2>
    <p>
        You sign in with your Google account and are responsible for what happens under it. We may suspend an
        account that is used to abuse the service, interfere with it, or break the law.
    </p>

    <h2>Your content</h2>
    <p>
        Decks and cards you create or import remain yours. You give us permission to store and process them only so
        that we can provide the service to you. Do not upload content you do not have the right to use.
    </p>

    <h2>Acceptable use</h2>
    <ul>
        <li>Do not try to access other people's accounts or data.</li>
        <li>Do not overload, probe or attack the service or its infrastructure.</li>
        <li>Do not use the service for anything unlawful.</li>
    </ul>

    <h2>Liability</h2>
    <p>
        To the extent the law allows, we are not liable for any loss arising from your use of MemFlash, including
        lost study progress.
    </p>

    <h2>Privacy</h2>
    <p>
        How we handle your data is described in the <a href="{{ route('privacy') }}">Privacy Policy</a>.
    </p>

    <h2>Contact</h2>
    <p>
        @if (config('app.contact_email'))
            Questions about these terms: <a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>.
        @else
            Questions about these terms can be sent to the MemFlash operator through the support address shown on
            Google's sign-in screen.
        @endif
    </p>
</x-legal.page>
