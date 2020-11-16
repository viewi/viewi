function call_user_func_array (cb, parameters) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  var func
  var scope = null
  var validJSFunctionNamePattern = /^[_$a-zA-Z\xA0-\uFFFF][_$a-zA-Z0-9\xA0-\uFFFF]*$/
  if (typeof cb === 'string') {
    if (typeof $global[cb] === 'function') {
      func = $global[cb]
    } else if (cb.match(validJSFunctionNamePattern)) {
      func = (new Function(null, 'return ' + cb)()) 
    }
  } else if (Object.prototype.toString.call(cb) === '[object Array]') {
    if (typeof cb[0] === 'string') {
      if (cb[0].match(validJSFunctionNamePattern)) {
        func = eval(cb[0] + "['" + cb[1] + "']") 
      }
    } else {
      func = cb[0][cb[1]]
    }
    if (typeof cb[0] === 'string') {
      if (typeof $global[cb[0]] === 'function') {
        scope = $global[cb[0]]
      } else if (cb[0].match(validJSFunctionNamePattern)) {
        scope = eval(cb[0]) 
      }
    } else if (typeof cb[0] === 'object') {
      scope = cb[0]
    }
  } else if (typeof cb === 'function') {
    func = cb
  }
  if (typeof func !== 'function') {
    throw new Error(func + ' is not a valid function')
  }
  return func.apply(scope, parameters)
}
