function array_slice (arr, offst, lgth, preserveKeys) { 
  var isInt = window.is_int
  var key = ''
  if (Object.prototype.toString.call(arr) !== '[object Array]' || (preserveKeys && offst !== 0)) {
    var lgt = 0
    var newAssoc = {}
    for (key in arr) {
      lgt += 1
      newAssoc[key] = arr[key]
    }
    arr = newAssoc
    offst = (offst < 0) ? lgt + offst : offst
    lgth = lgth === undefined ? lgt : (lgth < 0) ? lgt + lgth - offst : lgth
    var assoc = {}
    var start = false
    var it = -1
    var arrlgth = 0
    var noPkIdx = 0
    for (key in arr) {
      ++it
      if (arrlgth >= lgth) {
        break
      }
      if (it === offst) {
        start = true
      }
      if (!start) {
        continue
      }++arrlgth
      if (isInt(key) && !preserveKeys) {
        assoc[noPkIdx++] = arr[key]
      } else {
        assoc[key] = arr[key]
      }
    }
    return assoc
  }
  if (lgth === undefined) {
    return arr.slice(offst)
  } else if (lgth >= 0) {
    return arr.slice(offst, offst + lgth)
  } else {
    return arr.slice(offst, lgth)
  }
}
