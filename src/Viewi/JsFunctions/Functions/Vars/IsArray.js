function is_array (mixedVar) { 
  var _getFuncName = function (fn) {
    var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
    if (!name) {
      return '(Anonymous)'
    }
    return name[1]
  }
  var _isArray = function (mixedVar) {
    if (!mixedVar || typeof mixedVar !== 'object' || typeof mixedVar.length !== 'number') {
      return false
    }
    var len = mixedVar.length
    mixedVar[mixedVar.length] = 'bogus'
    if (len !== mixedVar.length) {
      mixedVar.length -= 1
      return true
    }
    delete mixedVar[mixedVar.length]
    return false
  }
  if (!mixedVar || typeof mixedVar !== 'object') {
    return false
  }
  var isArray = _isArray(mixedVar)
  if (isArray) {
    return true
  }
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.objectsAsArrays') : undefined) || 'on'
  if (iniVal === 'on') {
    var asString = Object.prototype.toString.call(mixedVar)
    var asFunc = _getFuncName(mixedVar.constructor)
    if (asString === '[object Object]' && asFunc === 'Object') {
      return true
    }
  }
  return false
}
