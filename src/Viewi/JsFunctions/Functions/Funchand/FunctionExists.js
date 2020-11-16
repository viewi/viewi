function function_exists (funcName) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  if (typeof funcName === 'string') {
    funcName = $global[funcName]
  }
  return typeof funcName === 'function'
}
