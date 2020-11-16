function array_reduce (aInput, callback) { 
  var lon = aInput.length
  var res = 0
  var i = 0
  var tmp = []
  for (i = 0; i < lon; i += 2) {
    tmp[0] = aInput[i]
    if (aInput[(i + 1)]) {
      tmp[1] = aInput[(i + 1)]
    } else {
      tmp[1] = 0
    }
    res += callback.apply(null, tmp)
    tmp = []
  }
  return res
}
