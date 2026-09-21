/**
 * The shared body of htmlspecialchars/htmlentities. flags are PHP's: ENT_HTML_QUOTE_SINGLE 1,
 * ENT_HTML_QUOTE_DOUBLE 2 (ENT_QUOTES = 3, ENT_NOQUOTES = 0), ENT_HTML5 48 (quote as &apos;).
 * PHP 8.1+ default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 = 11.
 * all: htmlentities - every character with a named HTML 4.01 entity, not just & < > " '.
 * doubleEncode false leaves existing entities (&amp; &#39; &#x27;) alone, if PHP knows the name.
 */
function _php_html_escape(str, flags, doubleEncode, all) {
  flags = flags === undefined || flags === null ? 11 : flags
  const table = all ? _php_html_entities() : { '&': '&amp;', '<': '&lt;', '>': '&gt;' }
  const single = (flags & 48) === 48 ? '&apos;' : '&#039;'
  // only entities PHP knows stay unencoded: &bogus; still becomes &amp;bogus;
  const knownNames = doubleEncode === false ? Object.values(_php_html_entities()) : []
  str = _phpCastString(str)
  let out = ''
  for (let i = 0; i < str.length; i++) {
    const ch = str[i]
    if (ch === '&' && doubleEncode === false) {
      const entity = str.slice(i).match(/^&(#\d+|#[xX][0-9a-fA-F]+|[A-Za-z][A-Za-z0-9]*);/)
      if (entity && (entity[1][0] === '#' || knownNames.indexOf('&' + entity[1] + ';') !== -1)) {
        out += entity[0]
        i += entity[0].length - 1
        continue
      }
    }
    if (ch === '"') {
      out += flags & 2 ? '&quot;' : ch
    } else if (ch === "'") {
      out += flags & 1 ? single : ch
    } else if (Object.prototype.hasOwnProperty.call(table, ch)) {
      out += table[ch]
    } else {
      out += ch
    }
  }
  return out
}
