function is_callable (mixedVar, syntaxOnly, callableName) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  var validJSFunctionNamePattern = /^[_$a-zA-Z\xA0-\uFFFF][_$a-zA-Z0-9\xA0-\uFFFF]*$/
  var name = ''
  var obj = {}
  var method = ''
  var validFunctionName = false
  var getFuncName = function (fn) {
    var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
    if (!name) {
      return '(Anonymous)'
    }
    return name[1]
  }
  if (/(^class|\(this\,)/.test(mixedVar.toString())) {
    return false
  }
  if (typeof mixedVar === 'string') {
    obj = $global
    method = mixedVar
    name = mixedVar
    validFunctionName = !!name.match(validJSFunctionNamePattern)
  } else if (typeof mixedVar === 'function') {
    return true
  } else if (Object.prototype.toString.call(mixedVar) === '[object Array]' &&
    mixedVar.length === 2 &&
    typeof mixedVar[0] === 'object' &&
    typeof mixedVar[1] === 'string') {
    obj = mixedVar[0]
    method = mixedVar[1]
    name = (obj.constructor && getFuncName(obj.constructor)) + '::' + method
  }
  if (syntaxOnly || typeof obj[method] === 'function') {
    if (callableName) {
      $global[callableName] = name
    }
    return true
  }
  if (validFunctionName && typeof eval(method) === 'function') { 
    if (callableName) {
      $global[callableName] = name
    }
    return true
  }
  return false
}
