function reset (arr) {
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.pointers = $locutus.php.pointers || []
  var pointers = $locutus.php.pointers
  var indexOf = function (value) {
    for (var i = 0, length = this.length; i < length; i++) {
      if (this[i] === value) {
        return i
      }
    }
    return -1
  }
  if (!pointers.indexOf) {
    pointers.indexOf = indexOf
  }
  if (pointers.indexOf(arr) === -1) {
    pointers.push(arr, 0)
  }
  var arrpos = pointers.indexOf(arr)
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    for (var k in arr) {
      if (pointers.indexOf(arr) === -1) {
        pointers.push(arr, 0)
      } else {
        pointers[arrpos + 1] = 0
      }
      return arr[k]
    }
    return false
  }
  if (arr.length === 0) {
    return false
  }
  pointers[arrpos + 1] = 0
  return arr[pointers[arrpos + 1]]
}
