function is_int (mixedVar) { 
  return mixedVar === +mixedVar && isFinite(mixedVar) && !(mixedVar % 1)
}
