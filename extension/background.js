import { translate, getSettings } from "./translate.js";
import { session, saveWord, pickList, signInUrl, lookUp, lookupPageUrl, serverUrl } from "./memflash.js";
import { speak } from "./tts.js";

chrome.runtime.onInstalled.addListener(() => {
  chrome.contextMenus.create({
    id: "translate-selection",
    title: "Translate \"%s\" with MemFlash",
    contexts: ["selection"],
  });
});

// The content script owns the on-page popup; we just tell it what to translate.
function showInTab(tabId, text) {
  if (text?.trim()) chrome.tabs.sendMessage(tabId, { type: "show-translation", text });
}

chrome.contextMenus.onClicked.addListener((info, tab) => {
  if (info.menuItemId === "translate-selection") showInTab(tab.id, info.selectionText);
});

chrome.commands.onCommand.addListener(async (command, tab) => {
  if (command !== "translate-selection") return;
  const [{ result }] = await chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: () => window.getSelection().toString(),
  });
  showInTab(tab.id, result);
});

const MEMFLASH_LANGUAGES = ["en", "fa"];

// Google Translate first (every language, dictionary entries); MemFlash's own lookup
// when Google fails and the pair is English ↔ Persian.
async function translateAnywhere(text, target, source) {
  try {
    return await translate(text, target, source);
  } catch (err) {
    const pairOk = MEMFLASH_LANGUAGES.includes(target) && (source === "auto" || MEMFLASH_LANGUAGES.includes(source));
    if (!pairOk) throw err;
    try {
      return await lookUp(text, source, target);
    } catch {
      throw err;
    }
  }
}

// All network and storage calls live in the worker; the popup, options page and
// content script talk to it through these messages.
const handlers = {
  async translate({ text, target, source }) {
    if (!target) ({ targetLang: target } = await getSettings());
    const result = await translateAnywhere(text, target, source);
    return { ...result, lookupUrl: await lookupPageUrl(result) };
  },
  // Saves into the MemFlash list picked last (or listId). Signed out: opens the sign-in page.
  async "save-word"({ result, listId }) {
    try {
      const res = await saveWord(result, listId);
      return { saved: true, created: res.created, message: res.message, list: res.list };
    } catch (err) {
      if (err.signedOut) {
        chrome.tabs.create({ url: await signInUrl() });
        throw new Error("Sign in to MemFlash in the tab that opened, then save again");
      }
      throw err;
    }
  },
  async session() {
    try {
      return await session();
    } catch (err) {
      if (err.signedOut) return { signed_in: false };
      throw err;
    }
  },
  async "pick-list"({ listId }) {
    return pickList(listId);
  },
  async "sign-in"() {
    await chrome.tabs.create({ url: await signInUrl() });
    return {};
  },
  async "open-memflash"({ path = "/dashboard" }) {
    await chrome.tabs.create({ url: (await serverUrl()) + path });
    return {};
  },
  async speak({ text, lang }) {
    await speak(text, lang);
    return {};
  },
  async "open-options"() {
    await chrome.runtime.openOptionsPage();
    return {};
  },
};

chrome.runtime.onMessage.addListener((msg, _sender, sendResponse) => {
  const handler = handlers[msg.type];
  if (!handler) return;
  handler(msg)
    .then(sendResponse)
    .catch((err) => sendResponse({ error: err.message }));
  return true;
});
