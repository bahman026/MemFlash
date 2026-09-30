{{--
    Suggests adding MemFlash to the home screen.

    iOS has no install API, so iPhone and iPad users get the two manual steps
    (Share, then Add to Home Screen). Browsers that fire `beforeinstallprompt`
    (Chrome, Edge, Android) get a one-tap Install button instead. Never shown
    inside the installed app, and snoozed for two weeks after "Not now".

    Styled here rather than with Tailwind utilities: public/build is only compiled
    when it is missing, so new utility classes would not reach a deployed server
    until someone rebuilt the assets.
--}}
<div id="pwa-install" role="dialog" aria-labelledby="pwa-install-title" hidden>
    <div class="pwa-install__card">
        <img src="{{ asset('icons/icon-192.png') }}" alt="" class="pwa-install__icon" width="48" height="48">
        <div class="pwa-install__body">
            <p id="pwa-install-title" class="pwa-install__title">Install MemFlash</p>
            <p class="pwa-install__text" data-pwa-mode="ios" hidden>
                Tap
                <svg class="pwa-install__share" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-label="Share">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0-12L8 7m4-4l4 4M6 11H5a1 1 0 00-1 1v8a1 1 0 001 1h14a1 1 0 001-1v-8a1 1 0 00-1-1h-1"/>
                </svg>
                <strong>Share</strong>, then <strong>Add to Home Screen</strong> to study like an app, even offline.
            </p>
            <p class="pwa-install__text" data-pwa-mode="prompt" hidden>
                Add it to your home screen to study like an app, even offline.
            </p>
            <div class="pwa-install__actions">
                <button type="button" class="pwa-install__primary" data-pwa-install hidden>Install</button>
                <button type="button" class="pwa-install__secondary" data-pwa-dismiss>Not now</button>
            </div>
        </div>
    </div>
</div>

<style>
    #pwa-install {
        position: fixed;
        inset: auto 0 0 0;
        z-index: 60;
        padding: 12px 12px max(12px, env(safe-area-inset-bottom));
    }
    #pwa-install[hidden], #pwa-install [hidden] { display: none !important; }
    .pwa-install__card {
        display: flex;
        gap: 12px;
        max-width: 28rem;
        margin: 0 auto;
        padding: 16px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 40px -12px rgba(15, 23, 42, .35), 0 0 0 1px rgba(15, 23, 42, .06);
    }
    .pwa-install__icon { flex: none; width: 48px; height: 48px; border-radius: 12px; }
    .pwa-install__body { flex: 1; min-width: 0; }
    .pwa-install__title { margin: 0; font-weight: 600; color: #111827; }
    .pwa-install__text { margin: 2px 0 0; font-size: 14px; line-height: 1.45; color: #4b5563; }
    .pwa-install__text strong { color: #111827; font-weight: 600; }
    .pwa-install__share { display: inline-block; width: 18px; height: 18px; vertical-align: -3px; color: #2563eb; }
    .pwa-install__actions { display: flex; gap: 8px; margin-top: 12px; }
    .pwa-install__actions button {
        min-height: 40px;
        padding: 0 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .pwa-install__primary { background: #2563eb; color: #fff; border: 0; }
    .pwa-install__secondary { background: #f3f4f6; color: #374151; border: 0; }
</style>

<script>
    (() => {
        const root = document.getElementById('pwa-install');
        if (!root) return;

        const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (standalone) return;

        const KEY = 'memflash:pwa-install-dismissed-at';
        const SNOOZE_MS = 14 * 24 * 60 * 60 * 1000;

        // Storage can be unavailable (private mode, blocked site data); then the
        // banner simply comes back next visit.
        let dismissedAt = 0;
        try { dismissedAt = Number(localStorage.getItem(KEY)) || 0; } catch (e) {}
        if (Date.now() - dismissedAt < SNOOZE_MS) return;

        const show = (mode) => {
            root.querySelectorAll('[data-pwa-mode]').forEach((el) => { el.hidden = el.dataset.pwaMode !== mode; });
            root.querySelector('[data-pwa-install]').hidden = mode !== 'prompt';
            root.hidden = false;
        };

        const dismiss = () => {
            try { localStorage.setItem(KEY, String(Date.now())); } catch (e) {}
            root.hidden = true;
        };

        root.querySelector('[data-pwa-dismiss]').addEventListener('click', dismiss);

        // iPadOS 13+ reports itself as a Mac; the touch points give it away.
        const ios = /iphone|ipad|ipod/i.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        if (ios) {
            setTimeout(() => show('ios'), 1500);
            return;
        }

        let deferred = null;

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferred = event;
            show('prompt');
        });

        root.querySelector('[data-pwa-install]').addEventListener('click', async () => {
            if (!deferred) return;
            deferred.prompt();
            const { outcome } = await deferred.userChoice;
            deferred = null;
            outcome === 'accepted' ? (root.hidden = true) : dismiss();
        });

        window.addEventListener('appinstalled', () => { root.hidden = true; });
    })();
</script>
