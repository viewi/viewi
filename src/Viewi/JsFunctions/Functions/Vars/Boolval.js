function boolval (mixedVar) {
  if (mixedVar === false) {
    return false
  }
  if (mixedVar === 0 || mixedVar === 0.0) {
    return false
  }
  if (mixedVar === '' || mixedVar === '0') {
    return false
  }
  if (Array.isArray(mixedVar) && mixedVar.length === 0) {
    return false
  }
  if (mixedVar === null || mixedVar === undefined) {
    return false
  }
  return true
}
