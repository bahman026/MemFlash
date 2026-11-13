export const LANGUAGES = {
  auto: "Detect language",
  ar: "Arabic",
  zh: "Chinese (Simplified)",
  nl: "Dutch",
  en: "English",
  fr: "French",
  de: "German",
  hi: "Hindi",
  it: "Italian",
  ja: "Japanese",
  ko: "Korean",
  fa: "Persian",
  pl: "Polish",
  pt: "Portuguese",
  ru: "Russian",
  es: "Spanish",
  sv: "Swedish",
  tr: "Turkish",
  uk: "Ukrainian",
  ur: "Urdu",
};

const displayNames = new Intl.DisplayNames(["en"], { type: "language", fallback: "code" });

// Falls back to the browser's name for codes Google detects that aren't in our list.
export function languageName(code) {
  if (LANGUAGES[code]) return LANGUAGES[code];
  try {
    return displayNames.of(code);
  } catch {
    return code;
  }
}

export function fillSelect(select, { includeAuto = false, value } = {}) {
  for (const [code, name] of Object.entries(LANGUAGES)) {
    if (code === "auto" && !includeAuto) continue;
    select.add(new Option(name, code, false, code === value));
  }
}
