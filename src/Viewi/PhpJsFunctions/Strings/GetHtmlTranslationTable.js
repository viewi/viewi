function get_html_translation_table(table, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.get-html-translation-table.php
  // table: HTML_SPECIALCHARS 0 (default) or HTML_ENTITIES 1; flags as in htmlspecialchars
  // (default ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401). Character → entity, in PHP's order.
  flags = flags === undefined || flags === null ? 11 : flags
  const special = { '"': 1, '&': 1, "'": 1, '<': 1, '>': 1 }
  const all = _php_html_entities()
  const result = {}
  for (const ch in all) {
    if (!table && !special[ch]) {
      continue
    }
    if ((ch === '"' && !(flags & 2)) || (ch === "'" && !(flags & 1))) {
      continue
    }
    result[ch] = ch === "'" && (flags & 48) === 48 ? '&apos;' : all[ch]
  }
  return result
}
