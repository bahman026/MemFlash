<x-legal.page title="Privacy Policy" updated="30 September 2026">
    <p>
        MemFlash (<a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>) is a flashcard app for learning
        English vocabulary with Persian translations. This page explains what we store about you, why, and what you
        can do about it.
    </p>

    <h2>What we collect</h2>
    <ul>
        <li>
            <strong>From Google Sign-In:</strong> your name, email address and profile picture. We use them to create
            your account, sign you in and show who is signed in. We do not receive your Google password, and we do
            not access your Gmail, Drive, contacts or any other Google data.
        </li>
        <li>
            <strong>What you create:</strong> your decks and cards, including cards imported from CSV or Excel files.
            We keep the cards; the uploaded file itself is read once and not stored.
        </li>
        <li>
            <strong>Your study history:</strong> each answer you give (the rating, when you gave it and how long it
            took), your level and your study settings. This is what schedules your next reviews.
        </li>
    </ul>

    <h2>How we use it</h2>
    <p>
        Only to run MemFlash for you: signing you in, scheduling reviews, and tuning the scheduling to your own
        review history. We do not sell your data, show ads, use analytics or tracking services, or share your data
        with anyone except the hosting provider that runs our server.
    </p>

    <h2>Google user data</h2>
    <p>
        MemFlash's use and transfer of information received from Google APIs adheres to the
        <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">Google API Services User Data Policy</a>,
        including the Limited Use requirements. We request only your basic profile and email address.
    </p>

    <h2>Cookies and storage on your device</h2>
    <ul>
        <li>A session cookie and a security (CSRF) cookie, which are needed to keep you signed in safely.</li>
        <li>A "remember me" cookie, so you stay signed in between visits.</li>
        <li>
            Offline copies of your cards and any answers not yet synced, kept in your browser so you can study
            without a connection. Clearing this site's data in your browser removes them.
        </li>
    </ul>
    <p>
        Pronunciation uses your browser's built-in speech feature; the word is not sent to us for that.
    </p>

    <h2>How long we keep it</h2>
    <p>
        As long as your account exists. If you ask us to delete your account, we delete your profile, decks, cards
        and study history.
    </p>

    <h2>Your choices</h2>
    <p>
        You can ask for a copy of your data, a correction, or deletion of your account at any time. You can also
        export any of your decks to CSV from the deck page, and revoke MemFlash's access in your
        <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">Google account settings</a>.
    </p>

    <h2>Contact</h2>
    <p>
        @if (config('app.contact_email'))
            Questions or requests: <a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>.
        @else
            Questions or requests can be sent to the MemFlash operator through the support address shown on
            Google's sign-in screen.
        @endif
    </p>

    <h2>Changes</h2>
    <p>
        If this policy changes, we will update the date at the top of this page.
    </p>
</x-legal.page>
