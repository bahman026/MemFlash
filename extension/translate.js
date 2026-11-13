// Shared translation helper. Uses Google's free public endpoint (unofficial, rate-limited).
// For production use, swap this for the Cloud Translation API with an API key.
//   dt=t  → translation, dt=rm → transliteration/phonetics, dt=bd → dictionary entries
export async function translate(text, target = "en", source = "auto") {
  const url = new URL("https://translate.googleapis.com/translate_a/single");
  url.search = new URLSearchParams({ client: "gtx", sl: source, tl: target, q: text });
  for (const dt of ["t", "rm", "bd"]) url.searchParams.append("dt", dt);

  const res = await fetch(url);
  if (!res.ok) throw new Error(`Translate request failed: ${res.status}`);
  let data;
  try {
    data = await res.json();
  } catch {
    throw new Error("Google Translate is rate-limiting requests, try again later");
  }

  const chunks = data[0] ?? [];
  // The transliteration chunk has no translated text: [null, null, targetTranslit, sourceTranslit]
  const translitChunk = chunks.find((c) => c[0] == null) ?? [];
  return {
    original: text,
    text: chunks.filter((c) => c[0] != null).map((c) => c[0]).join(""),
    detectedSource: data[2] ?? source,
    target,
    translit: translitChunk[2] ?? "",
    srcTranslit: translitChunk[3] ?? "",
    dictionary: (data[1] ?? []).map((entry) => ({ pos: entry[0], terms: entry[1] ?? [] })),
  };
}

// MemFlash is an English → Persian trainer, so Persian is the default target.
// server: the MemFlash site words are saved to (changeable for local development).
export const DEFAULT_SETTINGS = { targetLang: "fa", popupMode: "icon", server: "https://memflash.ir" };

export async function getSettings() {
  return chrome.storage.sync.get(DEFAULT_SETTINGS);
}
