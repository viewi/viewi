function strnatcasecmp (a, b) {
  var strnatcmp = strnatcmp
  var _phpCastString = _phpCastString
  if (arguments.length !== 2) {
    return null
  }
  return strnatcmp(_phpCastString(a).toLowerCase(), _phpCastString(b).toLowerCase())
}
