import { fillSelect } from "../languages.js";
import { getSettings } from "../translate.js";
import { fillLists } from "../lists.js";

const $ = (id) => document.getElementById(id);
const send = (msg) => chrome.runtime.sendMessage(msg);
const { targetLang } = await getSettings();
fillSelect($("source"), { includeAuto: true, value: "auto" });
fillSelect($("target"), { value: targetLang });

let lastResult = null;
let account = null;

$("swap").addEventListener("click", () => {
  if ($("source").value === "auto") return;
  [$("source").value, $("target").value] = [$("target").value, $("source").value];
});

function renderStar(saved) {
  $("star").textContent = saved ? "★" : "☆";
  $("star").classList.toggle("saved", saved);
  $("star").title = saved ? "Saved to MemFlash" : "Save to my MemFlash word list";
}

async function run() {
  const text = $("input").value.trim();
  if (!text) return;
  $("error").textContent = "";
  $("notice").textContent = "";
  $("result").hidden = false;
  $("output").textContent = "…";
  $("dict").replaceChildren();
  const res = await send({ type: "translate", text, source: $("source").value, target: $("target").value });
  if (res.error) {
    $("result").hidden = true;
    $("error").textContent = res.error;
    return;
  }
  lastResult = res;
  $("output").textContent = res.text;
  renderStar(false);
  for (const d of res.dictionary) {
    const row = document.createElement("div");
    row.dir = "auto";
    if (d.pos) row.append(Object.assign(document.createElement("span"), { className: "pos", textContent: d.pos }));
    row.append(d.terms.join(", "));
    $("dict").append(row);
  }
}

$("translate").addEventListener("click", run);
$("input").addEventListener("keydown", (e) => {
  if (e.key === "Enter" && (e.metaKey || e.ctrlKey)) run();
});

$("star").addEventListener("click", async () => {
  if (!lastResult || $("star").classList.contains("saved")) return;
  $("star").disabled = true;
  $("error").textContent = "";
  const res = await send({ type: "save-word", result: lastResult });
  $("star").disabled = false;
  if (res.error) {
    $("error").textContent = res.error;
    return;
  }
  renderStar(true);
  $("notice").textContent = res.message;
});

// --- My words tab: the MemFlash list words are saved to ---

function renderSession(s) {
  account = s.signed_in ? s : null;
  $("account").textContent = s.signed_in ? s.user.email : "Not signed in to MemFlash";
  $("saved-signin").hidden = s.signed_in;
  $("saved-lists").hidden = !s.signed_in;
  $("open-list").hidden = !s.signed_in || !s.list_id;
  $("empty").hidden = !s.signed_in || s.words.length > 0;
  if (!s.signed_in) {
    $("words").replaceChildren();
    return;
  }

  fillLists($("list"), s);

  $("words").replaceChildren(...s.words.map((w) => {
    const li = document.createElement("li");
    const text = document.createElement("div");
    text.className = "text";
    text.dir = "auto";
    text.append(
      Object.assign(document.createElement("div"), { className: "term", textContent: w.front }),
      Object.assign(document.createElement("div"), { textContent: w.back }),
    );
    li.append(text);
    return li;
  }));
}

async function showSaved() {
  $("error-saved").textContent = "";
  const s = await send({ type: "session" });
  if (s.error) {
    $("error-saved").textContent = s.error;
    return;
  }
  renderSession(s);
}

$("list").addEventListener("change", async () => {
  const value = $("list").value;
  const res = await send({ type: "choose-list", listId: value === "" ? null : Number(value) });
  if (res.error) $("error-saved").textContent = res.error;
  else showSaved();
});

$("open-list").addEventListener("click", (e) => {
  e.preventDefault();
  if (account?.list_id) send({ type: "open-memflash", path: `/decks/${account.list_id}` });
});

function switchTab(name) {
  $("tab-translate").classList.toggle("active", name === "translate");
  $("tab-saved").classList.toggle("active", name === "saved");
  $("view-translate").hidden = name !== "translate";
  $("view-saved").hidden = name !== "saved";
  if (name === "saved") showSaved();
}

$("tab-translate").addEventListener("click", () => switchTab("translate"));
$("tab-saved").addEventListener("click", () => switchTab("saved"));

$("signin").addEventListener("click", () => send({ type: "sign-in" }));

$("options").addEventListener("click", (e) => {
  e.preventDefault();
  chrome.runtime.openOptionsPage();
});

send({ type: "session" }).then((s) => {
  if (!s.error) renderSession(s);
});
