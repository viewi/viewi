function array_splice (arr, offst, lgth, replacement) { 
  var isInt = window.is_int
  var _checkToUpIndices = function (arr, ct, key) {
    if (arr[ct] !== undefined) {
      var tmp = ct
      ct += 1
      if (ct === key) {
        ct += 1
      }
      ct = _checkToUpIndices(arr, ct, key)
      arr[ct] = arr[tmp]
      delete arr[tmp]
    }
    return ct
  }
  if (replacement && typeof replacement !== 'object') {
    replacement = [replacement]
  }
  if (lgth === undefined) {
    lgth = offst >= 0 ? arr.length - offst : -offst
  } else if (lgth < 0) {
    lgth = (offst >= 0 ? arr.length - offst : -offst) + lgth
  }
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    var lgt = 0
    var ct = -1
    var rmvd = []
    var rmvdObj = {}
    var replCt = -1
    var intCt = -1
    var returnArr = true
    var rmvdCt = 0
    var key = ''
    for (key in arr) {
      lgt += 1
    }
    offst = (offst >= 0) ? offst : lgt + offst
    for (key in arr) {
      ct += 1
      if (ct < offst) {
        if (isInt(key)) {
          intCt += 1
          if (parseInt(key, 10) === intCt) {
            continue
          }
          _checkToUpIndices(arr, intCt, key)
          arr[intCt] = arr[key]
          delete arr[key]
        }
        continue
      }
      if (returnArr && isInt(key)) {
        rmvd.push(arr[key])
        rmvdObj[rmvdCt++] = arr[key]
      } else {
        rmvdObj[key] = arr[key]
        returnArr = false
      }
      if (replacement && replacement[++replCt]) {
        arr[key] = replacement[replCt]
      } else {
        delete arr[key]
      }
    }
    return returnArr ? rmvd : rmvdObj
  }
  if (replacement) {
    replacement.unshift(offst, lgth)
    return Array.prototype.splice.apply(arr, replacement)
  }
  return arr.splice(offst, lgth)
}
