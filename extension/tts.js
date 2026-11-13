// Pronunciation with Google Translate's voices (the same unofficial endpoint family as
// translate.js). A service worker can't play audio, so playback happens in an offscreen
// document. Languages Google has no voice for (it answers 400) fall back to chrome.tts.

// Google's TTS endpoint rejects text over 200 characters, so longer text is spoken in
// pieces, broken after a sentence end where possible, else at a space.
function chunks(text, max = 200) {
  const parts = [];
  let rest = text.replace(/\s+/g, " ").trim();
  while (rest.length > max) {
    const head = rest.slice(0, max + 1);
    let cut = head.match(/^.*(?:[.!?;؟](?= )|[。！？])/)?.[0].length ?? 0;
    if (cut < max / 3) cut = head.lastIndexOf(" ");
    if (cut <= 0) cut = max;
    parts.push(rest.slice(0, cut).trim());
    rest = rest.slice(cut).trim();
  }
  if (rest) parts.push(rest);
  return parts;
}

function ttsUrl(text, lang) {
  const url = new URL("https://translate.googleapis.com/translate_tts");
  url.search = new URLSearchParams({ client: "gtx", ie: "UTF-8", tl: lang, q: text });
  return url.href;
}

let creating = null;

async function ensureOffscreen() {
  const contexts = await chrome.runtime.getContexts({ contextTypes: ["OFFSCREEN_DOCUMENT"] });
  if (contexts.length) return;
  creating ??= chrome.offscreen
    .createDocument({
      url: "offscreen/offscreen.html",
      reasons: ["AUDIO_PLAYBACK"],
      justification: "Play Google Translate pronunciation audio",
    })
    .finally(() => (creating = null));
  await creating;
}

let latest = 0;

export async function speak(text, lang) {
  const id = ++latest;
  chrome.tts.stop();
  const urls = chunks(text).map((part) => ttsUrl(part, lang));
  if (!urls.length) return;
  await ensureOffscreen();
  const res = await chrome.runtime
    .sendMessage({ target: "offscreen", type: "play", urls })
    .catch((err) => ({ error: err.message }));
  // Only fall back if no newer speak() has started meanwhile.
  if (res?.error && id === latest) chrome.tts.speak(text, { lang, rate: 0.9 });
}
