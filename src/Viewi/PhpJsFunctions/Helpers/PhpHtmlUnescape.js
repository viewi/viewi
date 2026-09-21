/**
 * The shared body of htmlspecialchars_decode/html_entity_decode, in one pass so &amp;lt; stays
 * &lt;. flags as in _php_html_escape (default 11). all: html_entity_decode - every named
 * HTML 4.01 entity and any numeric entity; otherwise only & < > and the quotes the flags allow.
 * &apos; is an HTML5/XML entity: decoded only with ENT_HTML5 (48), as in PHP.
 */
function _php_html_unescape(str, flags, all) {
  flags = flags === undefined || flags === null ? 11 : flags
  const named = { amp: '&', lt: '<', gt: '>' }
  if (all) {
    const table = _php_html_entities()
    for (const ch in table) {
      const name = table[ch].match(/^&([A-Za-z][A-Za-z0-9]*);$/)
      if (name) {
        named[name[1]] = ch
      }
    }
  }
  delete named.quot
  if (flags & 2) {
    named.quot = '"'
  }
  if ((flags & 1) && (flags & 48) === 48) {
    named.apos = "'"
  }
  return _phpCastString(str).replace(/&(#\d+|#[xX][0-9a-fA-F]+|[A-Za-z][A-Za-z0-9]*);/g, function (entity, body) {
    if (body[0] !== '#') {
      return Object.prototype.hasOwnProperty.call(named, body) ? named[body] : entity
    }
    const code = body[1] === 'x' || body[1] === 'X' ? parseInt(body.slice(2), 16) : parseInt(body.slice(1), 10)
    if (code === 39 && !(flags & 1)) {
      return entity
    }
    if (code === 34 && !(flags & 2)) {
      return entity
    }
    if (!all && [34, 38, 39, 60, 62].indexOf(code) === -1) {
      return entity
    }
    return code > 0 && code <= 0x10ffff && (code < 0xd800 || code > 0xdfff) ? String.fromCodePoint(code) : entity
  })
}
