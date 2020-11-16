function setrawcookie (name, value, expires, path, domain, secure) {
  if (typeof window === 'undefined') {
    return true
  }
  if (typeof expires === 'string' && (/^\d+$/).test(expires)) {
    expires = parseInt(expires, 10)
  }
  if (expires instanceof Date) {
    expires = expires.toUTCString()
  } else if (typeof expires === 'number') {
    expires = (new Date(expires * 1e3)).toUTCString()
  }
  var r = [name + '=' + value]
  var i = ''
  var s = {
    expires: expires,
    path: path,
    domain: domain
  }
  for (i in s) {
    if (s.hasOwnProperty(i)) {
      s[i] && r.push(i + '=' + s[i])
    }
  }
  if (secure) {
    r.push('secure')
  }
  window.document.cookie = r.join(';')
  return true
}
