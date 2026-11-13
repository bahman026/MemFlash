// The MemFlash account: the extension uses the browser's MemFlash sign-in (its session
// cookie), so there is nothing to sign in to here. Signing in happens on the site.
import { getSettings } from "./translate.js";

export const DEFAULT_SERVER = "https://memflash.ir";

export async function serverUrl() {
  const { server } = await getSettings();
  return (server || DEFAULT_SERVER).replace(/\/+$/, "");
}

class SignedOut extends Error {
  constructor() {
    super("Sign in to MemFlash to save words");
    this.signedOut = true;
  }
}

async function call(path, { method = "GET", body, csrf } = {}) {
  const server = await serverUrl();
  let res;
  try {
    res = await fetch(server + path, {
      method,
      credentials: "include",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(body ? { "Content-Type": "application/json" } : {}),
        ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    });
  } catch {
    throw new Error(`Could not reach ${new URL(server).host}`);
  }
  // A signed-out request is sent to the sign-in page; 419 is an expired session.
  if (res.status === 401 || res.status === 419 || (res.redirected && new URL(res.url).pathname === "/login")) {
    throw new SignedOut();
  }
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const first = data.errors ? Object.values(data.errors).flat()[0] : null;
    throw new Error(first || data.message || `MemFlash answered ${res.status}`);
  }
  return data;
}

// Who is signed in, their lists and the newest words of the list words are saved to:
// the one chosen in the extension's settings, else the one picked last on the site.
// { signed_in, user, csrf, lists, list_id, chosen_list_found, words }
export async function session() {
  const { listId } = await getSettings();
  const s = await call(`/api/extension/session${listId ? `?list=${listId}` : ""}`);
  // The chosen list was deleted on the site: go back to following the site.
  if (s.signed_in && listId && !s.chosen_list_found) await chrome.storage.sync.set({ listId: null });
  return s;
}

export async function signInUrl() {
  return `${await serverUrl()}/login`;
}

// MemFlash cards are English on the front. A Persian word gets its English
// translation on the front; any other pair keeps the selected text on the front.
export function cardFor(result) {
  const persianToEnglish = result.detectedSource === "fa" && result.target === "en";
  const front = (persianToEnglish ? result.text : result.original).trim();
  const back = (persianToEnglish ? result.original : result.text).trim();
  const phonetic = result.detectedSource === "en" ? result.srcTranslit : persianToEnglish ? result.translit : "";
  const description = result.dictionary
    .slice(0, 3)
    .map((d) => `${d.pos}: ${d.terms.slice(0, 4).join(", ")}`)
    .join("\n");
  return {
    front: front.slice(0, 1000),
    back: back.slice(0, 1000),
    description: description ? description.slice(0, 2000) : null,
    pronunciation: phonetic ? phonetic.slice(0, 100) : null,
  };
}

// Saves into the list session() resolved: the extension's chosen list, else the site's.
export async function saveWord(result) {
  const s = await session();
  if (!s.signed_in) throw new SignedOut();
  return call("/api/word-lists/words", {
    method: "POST",
    csrf: s.csrf,
    body: { ...cardFor(result), list_id: s.list_id ?? "default" },
  });
}

// The extension's own setting; null follows the list picked last on the site.
export async function chooseList(listId) {
  await chrome.storage.sync.set({ listId: listId || null });
}

// MemFlash's own lookup, for English ↔ Persian when Google Translate can't be reached
// (it is often blocked from Iran). Answers in translate()'s shape.
export async function lookUp(text, source, target) {
  const params = new URLSearchParams({ q: text.slice(0, 100) });
  if (source && source !== "auto") params.set("sl", source);
  params.set("tl", target);
  const data = await call(`/api/words/lookup?${params}`);
  const english = data.language === "en";
  return {
    original: text,
    text: data.translations?.[0] ?? data.senses?.[0]?.definition ?? "",
    detectedSource: data.language,
    target: data.target,
    translit: "",
    srcTranslit: english ? data.phonetic ?? "" : "",
    dictionary: data.translations?.length > 1 ? [{ pos: "", terms: data.translations.slice(1) }] : [],
    via: "memflash",
  };
}

export async function lookupPageUrl(result) {
  const params = new URLSearchParams({ q: result.original.slice(0, 100) });
  const pair = `${result.detectedSource}-${result.target}`;
  if (["en-fa", "fa-en", "en-en"].includes(pair)) {
    params.set("sl", result.detectedSource);
    params.set("tl", result.target);
  }
  return `${await serverUrl()}/lookup?${params}`;
}
