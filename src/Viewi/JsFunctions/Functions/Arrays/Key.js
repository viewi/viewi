function key (arr) {
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
  var cursor = pointers[pointers.indexOf(arr) + 1]
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    var ct = 0
    for (var k in arr) {
      if (ct === cursor) {
        return k
      }
      ct++
    }
    return false
  }
  if (arr.length === 0) {
    return false
  }
  return cursor
}
