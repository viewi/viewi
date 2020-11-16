function array_shift (inputArr) { 
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
  if (inputArr.length === 0) {
    return null
  }
  if (inputArr.length > 0) {
    return inputArr.shift()
  }
}
