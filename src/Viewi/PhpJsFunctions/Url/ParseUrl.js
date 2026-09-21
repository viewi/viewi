function parse_url(url, component) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.parse-url.php
  // Keys in PHP's order (scheme, host, port, user, pass, path, query, fragment), port as an int,
  // only the parts present; false for a URL PHP can't parse ('http:///x'). component:
  // PHP_URL_SCHEME 0, HOST 1, PORT 2, USER 3, PASS 4, PATH 5, QUERY 6, FRAGMENT 7 → that part or null.
  url = _phpCastString(url)
  const parts = {}
  let rest = url
  const hash = rest.indexOf('#')
  let fragment
  if (hash !== -1) {
    fragment = rest.slice(hash + 1)
    rest = rest.slice(0, hash)
  }
  const q = rest.indexOf('?')
  let query
  if (q !== -1) {
    query = rest.slice(q + 1)
    rest = rest.slice(0, q)
  }
  const scheme = rest.match(/^([a-zA-Z][a-zA-Z0-9+.-]*):/)
  if (scheme && !/^\d+$/.test(rest.slice(scheme[0].length).split('/')[0])) {
    parts.scheme = scheme[1]
    rest = rest.slice(scheme[0].length)
  }
  if (rest.startsWith('//')) {
    const authorityEnd = rest.indexOf('/', 2)
    let authority = authorityEnd === -1 ? rest.slice(2) : rest.slice(2, authorityEnd)
    rest = authorityEnd === -1 ? '' : rest.slice(authorityEnd)
    if (authority === '') {
      return false
    }
    const at = authority.lastIndexOf('@')
    let user, pass
    if (at !== -1) {
      const credentials = authority.slice(0, at)
      authority = authority.slice(at + 1)
      const colon = credentials.indexOf(':')
      user = colon === -1 ? credentials : credentials.slice(0, colon)
      pass = colon === -1 ? undefined : credentials.slice(colon + 1)
    }
    const port = authority.match(/:(\d*)$/)
    if (port) {
      authority = authority.slice(0, -port[0].length)
    }
    parts.host = authority
    if (port && port[1] !== '') {
      parts.port = parseInt(port[1], 10)
    }
    if (user !== undefined) {
      parts.user = user
    }
    if (pass !== undefined) {
      parts.pass = pass
    }
  }
  if (rest !== '') {
    parts.path = rest
  }
  if (query !== undefined) {
    parts.query = query
  }
  if (fragment !== undefined) {
    parts.fragment = fragment
  }
  if (component !== undefined && component !== -1) {
    const names = ['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment']
    const value = parts[names[component]]
    return value === undefined ? null : value
  }
  const ordered = {}
  for (const key of ['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment']) {
    if (parts[key] !== undefined) {
      ordered[key] = parts[key]
    }
  }
  return ordered
}
