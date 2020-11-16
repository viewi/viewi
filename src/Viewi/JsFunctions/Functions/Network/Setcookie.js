function setcookie (name, value, expires, path, domain, secure) {
  var setrawcookie = require('../network/setrawcookie')
  return setrawcookie(name, encodeURIComponent(value), expires, path, domain, secure)
}
