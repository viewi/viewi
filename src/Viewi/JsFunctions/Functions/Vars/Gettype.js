function gettype (mixedVar) {
  var isFloat = window.is_float
  var s = typeof mixedVar
  var name
  var _getFuncName = function (fn) {
    var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
    if (!name) {
      return '(Anonymous)'
    }
    return name[1]
  }
  if (s === 'object') {
    if (mixedVar !== null) {
      if (typeof mixedVar.length === 'number' &&
        !(mixedVar.propertyIsEnumerable('length')) &&
        typeof mixedVar.splice === 'function') {
        s = 'array'
      } else if (mixedVar.constructor && _getFuncName(mixedVar.constructor)) {
        name = _getFuncName(mixedVar.constructor)
        if (name === 'Date') {
          s = 'date'
        } else if (name === 'RegExp') {
          s = 'regexp'
        } else if (name === 'LOCUTUS_Resource') {
          s = 'resource'
        }
      }
    } else {
      s = 'null'
    }
  } else if (s === 'number') {
    s = isFloat(mixedVar) ? 'double' : 'integer'
  }
  return s
}
