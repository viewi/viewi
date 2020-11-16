function isset () {
  var a = arguments
  var l = a.length
  var i = 0
  var undef
  if (l === 0) {
    throw new Error('Empty isset')
  }
  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false
    }
    i++
  }
  return true
}
