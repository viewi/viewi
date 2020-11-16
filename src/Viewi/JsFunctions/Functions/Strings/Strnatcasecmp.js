function strnatcasecmp (a, b) {
  var strnatcmp = require('../strings/strnatcmp')
  var _phpCastString = require('../_helpers/_phpCastString')
  if (arguments.length !== 2) {
    return null
  }
  return strnatcmp(_phpCastString(a).toLowerCase(), _phpCastString(b).toLowerCase())
}
