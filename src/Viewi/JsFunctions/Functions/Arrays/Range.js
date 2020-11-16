function range (low, high, step) {
  var matrix = []
  var iVal
  var endval
  var plus
  var walker = step || 1
  var chars = false
  if (!isNaN(low) && !isNaN(high)) {
    iVal = low
    endval = high
  } else if (isNaN(low) && isNaN(high)) {
    chars = true
    iVal = low.charCodeAt(0)
    endval = high.charCodeAt(0)
  } else {
    iVal = (isNaN(low) ? 0 : low)
    endval = (isNaN(high) ? 0 : high)
  }
  plus = !(iVal > endval)
  if (plus) {
    while (iVal <= endval) {
      matrix.push(((chars) ? String.fromCharCode(iVal) : iVal))
      iVal += walker
    }
  } else {
    while (iVal >= endval) {
      matrix.push(((chars) ? String.fromCharCode(iVal) : iVal))
      iVal -= walker
    }
  }
  return matrix
}
