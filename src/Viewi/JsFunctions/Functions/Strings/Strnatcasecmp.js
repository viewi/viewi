function strnatcasecmp (a, b) {
  var strnatcmp = window.strnatcmp
  var _phpCastString = window._phpCastString
  if (arguments.length !== 2) {
    return null
  }
  return strnatcmp(_phpCastString(a).toLowerCase(), _phpCastString(b).toLowerCase())
}
