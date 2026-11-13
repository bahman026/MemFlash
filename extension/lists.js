// The "Save words to" picker, shared by the popup and the options page. The first
// option follows the list picked last on the MemFlash site; the others pin one list.
export function fillLists(select, session) {
  select.replaceChildren(new Option(`Same as MemFlash (list picked last)`, ""));
  for (const list of session.lists) {
    const label = `${list.name}${list.is_default_list ? " (default)" : ""} · ${list.cards_count} words`;
    select.add(new Option(label, String(list.id)));
  }
  select.value = session.chosen_list_found ? String(session.list_id) : "";
}
