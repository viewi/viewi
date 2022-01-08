function setcookie (name, value, expires, path, domain, secure) {
  var setrawcookie = setrawcookie
  return setrawcookie(name, encodeURIComponent(value), expires, path, domain, secure)
}
