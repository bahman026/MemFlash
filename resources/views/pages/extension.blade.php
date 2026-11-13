<x-legal.page title="MemFlash for Chrome" updated="13 November 2026">
    <p>
        Translate any word on any web page and save it to your MemFlash word lists with one click. Saved words are
        studied in MemFlash like any other deck.
    </p>

    <p class="extension__download">
        <a href="{{ route('extension.download') }}" class="extension__button" download>Download the extension</a>
        <span>Version {{ $version }} · for Google Chrome (and Edge, Brave and other Chromium browsers)</span>
    </p>

    <h2>What it does</h2>
    <ul>
        <li><strong>Select text on a page:</strong> a MemFlash icon appears. Click it to see the translation, the pronunciation and dictionary entries.</li>
        <li><strong>Click the ☆ star</strong> to save the word to your MemFlash word list: the list you picked last in MemFlash's word lookup.</li>
        <li><strong>Right-click</strong> selected text, or press <strong>Alt+T</strong>, to translate it.</li>
        <li><strong>The toolbar button</strong> translates typed text and shows the newest words of your list.</li>
        <li>Words are translated into <strong>Persian</strong> by default. You can choose another language in the extension's options.</li>
    </ul>

    <h2>Install it</h2>
    <ol class="extension__steps">
        <li>Download the zip above and unzip it. You get a folder called <strong>memflash-extension</strong>.</li>
        <li>In Chrome, open <code>chrome://extensions</code> (type it in the address bar).</li>
        <li>Turn on <strong>Developer mode</strong> (top right).</li>
        <li>Click <strong>Load unpacked</strong> and choose the <strong>memflash-extension</strong> folder.</li>
        <li>Pin MemFlash from the puzzle-piece menu so its button stays in the toolbar.</li>
    </ol>
    <p>
        Keep the folder: Chrome loads the extension from it. To update, download the new zip, replace the folder's
        contents, and click the reload arrow on the MemFlash card in <code>chrome://extensions</code>.
    </p>

    <h2>Signing in</h2>
    <p>
        The extension uses your MemFlash sign-in in the same browser. If you are not signed in, clicking the star opens
        the <a href="{{ route('login.page') }}">sign-in page</a>; sign in there and click the star again.
    </p>

    <h2>Privacy</h2>
    <p>
        Text you translate is sent to Google Translate, or, when Google cannot be reached, to MemFlash's own lookup.
        Words you save go to your MemFlash account, as described in the <a href="{{ route('privacy') }}">Privacy Policy</a>.
        The extension sends nothing on its own: only text you select and ask it to translate.
    </p>

    <style>
        .legal ol.extension__steps { padding-left: 1.25rem; list-style: decimal; margin: 0 0 12px; }
        .legal ol.extension__steps li { margin-bottom: 6px; }
        .legal code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.9em; background: #f3f4f6; padding: 1px 5px; border-radius: 4px; }
        .extension__download { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px; margin: 20px 0 8px !important; }
        .extension__download span { font-size: 0.875rem; color: #6b7280; }
        .legal a.extension__button { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; font-weight: 600; padding: 10px 20px; border-radius: 8px; }
        .legal a.extension__button:hover { background: #1d4ed8; }
    </style>
</x-legal.page>
