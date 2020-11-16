function is_float (mixedVar) { 
  return +mixedVar === mixedVar && (!isFinite(mixedVar) || !!(mixedVar % 1))
}
