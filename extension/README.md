# MemFlash for Chrome

A Manifest V3 extension that translates text on any page and saves words to your **MemFlash**
word lists, where they are studied like any other deck. It began as the "Quick Translate"
extension (`workspace/chore/google-translate-extension`) and was renamed and wired to MemFlash
on 2026-11-13.

Users download it from **https://memflash.ir/extension**. That page zips this folder on the fly
(`ExtensionController::download()`), so the zip always matches the code here: edit, deploy, done.
Bump `version` in `manifest.json` with each change so users can tell they have the new one.

## Features
- **Select text on any page**: a MemFlash icon appears; click it for a card with the
  translation, phonetics, 🔊 pronunciation and dictionary entries.
- **☆ Star** saves the word to the MemFlash list picked last (the same list the site's lookup
  uses). English goes on the card's front: a Persian word is saved with its English translation
  on the front.
- **Toolbar popup**: a *Translate* tab, and a *My words* tab that picks the list and shows its
  newest words.
- **Right-click** selected text → "Translate … with MemFlash", or press `Alt+T`.
- **Options**: target language (Persian by default), pop-up behaviour, and the MemFlash address
  (only for local development).

## How it talks to MemFlash
No separate sign-in. The extension calls the site with the browser's own MemFlash session
cookie (`credentials: "include"`; Chrome sends cookies to hosts in `host_permissions`):

| Call | Purpose |
|---|---|
| `GET /api/extension/session` | `{signed_in, user, csrf, lists, list_id, words}`; never redirects |
| `POST /api/word-lists/words` | save a word (same endpoint as the site's lookup), CSRF from the session call |
| `PUT /api/word-lists/selected` | pick the list |
| `GET /api/words/lookup` | translation fallback for English ↔ Persian when Google Translate fails (often from Iran) |

Signed out, saving opens `/login` in a tab; after signing in there, the star works.

Translation and speech use Google's free `translate.googleapis.com` endpoints (unofficial,
rate-limited), as before.

## Load it in Chrome
1. Download and unzip it, or use this folder.
2. Open `chrome://extensions` and turn on **Developer mode**.
3. Click **Load unpacked** and pick the folder.

For local development, set the MemFlash address in Options to `http://127.0.0.1:5050`.

## Layout
```
manifest.json      extension config (MV3)
background.js      service worker: message router, context menu, shortcut
translate.js       translate() via Google + settings
memflash.js        MemFlash calls: session, save, pick list, lookup fallback
tts.js             pronunciation: Google Translate TTS, chrome.tts fallback
offscreen/         offscreen document that plays the TTS audio (workers can't)
languages.js       language list + <select> filler
content.js/.css    selection icon and translation card (shadow DOM)
popup/             toolbar popup: translate + my words
options/           settings page
icons/             MemFlash icon (16/32/48/128 px, from public/icons/icon-512.png)
```
