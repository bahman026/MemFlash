// On-page UI: a small icon next to the selected text and a translation card with a star to save
// the word. Everything lives in a shadow root so page styles can't leak in (or out).
// Content scripts can't be ES modules, so the shared language list is loaded with import().
const languagesReady = import(chrome.runtime.getURL("languages.js"));

let settings = { targetLang: "fa", popupMode: "icon" };
chrome.storage.sync.get(settings).then((s) => (settings = s));
chrome.storage.onChanged.addListener((changes, area) => {
  if (area !== "sync") return;
  for (const [key, { newValue }] of Object.entries(changes)) settings[key] = newValue;
});

const send = (msg) => chrome.runtime.sendMessage(msg);

let host = null;
let root = null;

async function ensureRoot() {
  if (host?.isConnected) return root;
  host = document.createElement("memflash-root");
  host.style.cssText = "all: initial; position: absolute; top: 0; left: 0; z-index: 2147483647;";
  root = host.attachShadow({ mode: "closed" });
  // A constructed stylesheet is not blocked by the page's Content-Security-Policy.
  const sheet = new CSSStyleSheet();
  sheet.replaceSync(await (await fetch(chrome.runtime.getURL("content.css"))).text());
  root.adoptedStyleSheets = [sheet];
  document.documentElement.appendChild(host);
  return root;
}

function closeAll() {
  root?.replaceChildren();
}

function selectionInfo() {
  const sel = window.getSelection();
  const text = sel.toString().trim();
  if (!text || !sel.rangeCount || text.length > 5000) return null;
  const rect = sel.getRangeAt(0).getBoundingClientRect();
  if (!rect.width && !rect.height) return null;
  return { text, rect };
}

// Converts a viewport rect to page coordinates so the UI scrolls with the text.
const pageRect = (r) => ({
  top: r.top + window.scrollY,
  bottom: r.bottom + window.scrollY,
  left: r.left + window.scrollX,
  right: r.right + window.scrollX,
  width: r.width,
});

function el(tag, props = {}, ...children) {
  const node = Object.assign(document.createElement(tag), props);
  node.append(...children.filter((c) => c != null));
  return node;
}

async function showIcon(text, rect) {
  const r = await ensureRoot();
  closeAll();
  const icon = el("button", { className: "icon", title: "Translate this" },
    el("img", { src: chrome.runtime.getURL("icons/icon48.png"), alt: "Translate with MemFlash" }));
  icon.style.top = `${rect.top - 34}px`;
  icon.style.left = `${rect.left + rect.width / 2 - 14}px`;
  icon.addEventListener("click", () => showCard(text, rect));
  r.append(icon);
}

async function showCard(text, rect, source = "auto") {
  const r = await ensureRoot();
  const { LANGUAGES, languageName } = await languagesReady;
  closeAll();

  const card = el("div", { className: "card" }, el("div", { className: "loading" }, "Translating…"));
  r.append(card);
  positionCard(card, rect);

  const result = await send({ type: "translate", text, source, target: settings.targetLang });
  if (!card.isConnected) return;
  if (result.error) {
    card.replaceChildren(closeButton(), el("div", { className: "error" }, result.error));
    return;
  }

  const sourceSelect = el("select", { className: "source" });
  for (const [code, name] of Object.entries(LANGUAGES)) {
    if (code !== "auto") sourceSelect.add(new Option(name, code, false, code === result.detectedSource));
  }
  if (!LANGUAGES[result.detectedSource]) {
    sourceSelect.add(new Option(languageName(result.detectedSource), result.detectedSource, true, true), 0);
  }
  sourceSelect.addEventListener("change", () => showCard(text, rect, sourceSelect.value));

  const star = el("button", { className: "star" });
  const status = el("div", { className: "status" });
  const renderStar = (saved) => {
    star.textContent = saved ? "★" : "☆";
    star.classList.toggle("saved", saved);
    star.title = saved ? "Saved to MemFlash" : "Save to my MemFlash word list";
  };
  renderStar(false);
  star.addEventListener("click", async () => {
    if (star.classList.contains("saved")) return;
    star.disabled = true;
    status.textContent = "";
    const res = await send({ type: "save-word", result });
    star.disabled = false;
    if (res.error) status.textContent = res.error;
    else {
      renderStar(true);
      status.textContent = res.message;
    }
  });

  const speaker = (txt, lang) => {
    const b = el("button", { className: "speak", title: "Listen", textContent: "🔊" });
    b.addEventListener("click", () => send({ type: "speak", text: txt, lang }));
    return b;
  };

  const dictionary = result.dictionary.length
    ? el("div", { className: "dict" },
        ...result.dictionary.map((d) =>
          el("div", { className: "dict-row" },
            el("span", { className: "pos" }, d.pos),
            el("span", { className: "terms", dir: "auto" }, d.terms.join(", ")))))
    : null;

  const optionsLink = el("a", { href: "#", textContent: "Options" });
  optionsLink.addEventListener("click", (e) => {
    e.preventDefault();
    send({ type: "open-options" });
  });

  card.replaceChildren(
    closeButton(),
    sourceSelect,
    el("div", { className: "word-row" },
      speaker(text, result.detectedSource),
      el("div", { className: "word", dir: "auto" }, text),
      star),
    result.srcTranslit ? el("div", { className: "phonetic" }, result.srcTranslit) : null,
    el("div", { className: "target-lang" }, languageName(result.target)),
    el("div", { className: "translation-row" },
      el("div", { className: "translation", dir: "auto" }, result.text),
      speaker(result.text, result.target)),
    result.translit ? el("div", { className: "translit" }, result.translit) : null,
    dictionary,
    status,
    el("div", { className: "footer" },
      optionsLink,
      el("a", { href: result.lookupUrl, target: "_blank", rel: "noopener", textContent: "Open in MemFlash »" })),
  );
  positionCard(card, rect);
}

function closeButton() {
  const b = el("button", { className: "close", title: "Close", textContent: "×" });
  b.addEventListener("click", closeAll);
  return b;
}

// Prefer below the selection; flip above when there is no room in the viewport.
function positionCard(card, rect) {
  const width = 300;
  const height = card.offsetHeight;
  const left = Math.max(window.scrollX + 8,
    Math.min(rect.left + rect.width / 2 - width / 2, window.scrollX + document.documentElement.clientWidth - width - 8));
  const below = rect.bottom + 10;
  const fitsBelow = below + height <= window.scrollY + window.innerHeight;
  card.style.left = `${left}px`;
  card.style.top = `${fitsBelow ? below : Math.max(window.scrollY + 8, rect.top - height - 10)}px`;
}

const insideUi = (e) => host && e.composedPath().includes(host);

document.addEventListener("mousedown", (e) => {
  if (!insideUi(e)) closeAll();
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeAll();
});

document.addEventListener("mouseup", (e) => {
  if (insideUi(e) || e.button !== 0 || settings.popupMode === "none") return;
  // Wait a tick so a click that clears the selection is already reflected.
  setTimeout(() => {
    const info = selectionInfo();
    if (!info) return;
    const rect = pageRect(info.rect);
    if (settings.popupMode === "immediate") showCard(info.text, rect);
    else showIcon(info.text, rect);
  });
});

// Context menu and keyboard shortcut (from the service worker) open the card directly.
chrome.runtime.onMessage.addListener((msg) => {
  if (msg.type !== "show-translation") return;
  const info = selectionInfo();
  const rect = info ? pageRect(info.rect)
    : { top: window.scrollY + 20, bottom: window.scrollY + 20, left: window.scrollX + 20, right: window.scrollX + 20, width: 0 };
  showCard(msg.text, rect);
});
