function is_scalar (mixedVar) { 
  return (/boolean|number|string/).test(typeof mixedVar)
}
