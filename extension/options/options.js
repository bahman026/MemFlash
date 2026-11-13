import { fillSelect } from "../languages.js";
import { getSettings, DEFAULT_SETTINGS } from "../translate.js";

const $ = (id) => document.getElementById(id);
const radios = () => document.querySelectorAll('input[name="popupMode"]');

function show({ targetLang, popupMode, server }) {
  $("target").value = targetLang;
  $("server").value = server;
  for (const r of radios()) r.checked = r.value === popupMode;
}

function setStatus(text, isError = false) {
  $("status").textContent = text;
  $("status").classList.toggle("error", isError);
}

fillSelect($("target"));
show(await getSettings());

$("save").addEventListener("click", async () => {
  const popupMode = [...radios()].find((r) => r.checked)?.value ?? DEFAULT_SETTINGS.popupMode;
  const server = $("server").value.trim().replace(/\/+$/, "") || DEFAULT_SETTINGS.server;
  await chrome.storage.sync.set({ targetLang: $("target").value, popupMode, server });
  setStatus("Options saved.");
  refreshAccount();
});

$("reset").addEventListener("click", async () => {
  await chrome.storage.sync.set(DEFAULT_SETTINGS);
  show(DEFAULT_SETTINGS);
  setStatus("Options reset.");
  refreshAccount();
});

let signedIn = false;

async function refreshAccount() {
  const s = await chrome.runtime.sendMessage({ type: "session" });
  signedIn = Boolean(s.signed_in);
  $("account").textContent = s.error
    ? s.error
    : s.signed_in
      ? `Signed in as ${s.user.email}. Starred words go to your MemFlash word list.`
      : "Not signed in. Sign in to MemFlash in this browser to save words with the ☆ star.";
  $("auth").textContent = signedIn ? "Open MemFlash" : "Sign in to MemFlash";
}

$("auth").addEventListener("click", () => {
  chrome.runtime.sendMessage(signedIn ? { type: "open-memflash" } : { type: "sign-in" });
});

refreshAccount();
