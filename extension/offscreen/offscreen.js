// Plays Google Translate speech for the service worker, which has no audio element of its own.
// Each "play" replaces whatever is playing. It answers once the first chunk starts (or fails),
// so the worker can fall back to chrome.tts for languages Google has no voice for.
const audio = new Audio();
let current = 0;

chrome.runtime.onMessage.addListener((msg, _sender, sendResponse) => {
  if (msg.target !== "offscreen" || msg.type !== "play") return;
  play(msg.urls, sendResponse);
  return true;
});

async function play(urls, respond) {
  const run = ++current;
  audio.pause();
  for (const [i, url] of urls.entries()) {
    audio.src = url;
    try {
      await audio.play();
    } catch (err) {
      // A newer play() aborts this one; only a real failure should trigger the fallback.
      if (i === 0) respond(run === current ? { error: err.message } : {});
      return;
    }
    if (i === 0) respond({});
    // "pause" fires both when a chunk ends and when a newer play() interrupts it.
    await new Promise((resolve) => audio.addEventListener("pause", resolve, { once: true }));
    if (run !== current) return;
  }
}
