function setcookie (name, value, expires, path, domain, secure) {
  var setrawcookie = window.setrawcookie
  return setrawcookie(name, encodeURIComponent(value), expires, path, domain, secure)
}
