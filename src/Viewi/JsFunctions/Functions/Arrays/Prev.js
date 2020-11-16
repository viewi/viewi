function prev (arr) {
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
  var arrpos = pointers.indexOf(arr)
  var cursor = pointers[arrpos + 1]
  if (pointers.indexOf(arr) === -1 || cursor === 0) {
    return false
  }
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    var ct = 0
    for (var k in arr) {
      if (ct === cursor - 1) {
        pointers[arrpos + 1] -= 1
        return arr[k]
      }
      ct++
    }
  }
  if (arr.length === 0) {
    return false
  }
  pointers[arrpos + 1] -= 1
  return arr[pointers[arrpos + 1]]
}
