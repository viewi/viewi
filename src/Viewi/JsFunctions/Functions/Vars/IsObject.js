function is_object (mixedVar) { 
  if (Object.prototype.toString.call(mixedVar) === '[object Array]') {
    return false
  }
  return mixedVar !== null && typeof mixedVar === 'object'
}
