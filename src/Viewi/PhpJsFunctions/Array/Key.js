function key (arr) {
  //  discuss at: https://locutus.io/php/key/
  // original by: Brett Zamir (https://brett-zamir.me)
  //    input by: Riddler (https://www.frontierwebdev.com/)
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //      note 1: Uses global: locutus to store the array pointer
  //   example 1: var $array = {fruit1: 'apple', 'fruit2': 'orange'}
  //   example 1: key($array)
  //   returns 1: 'fruit1'

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.pointers = $locutus.php.pointers || []
  const pointers = $locutus.php.pointers

  const indexOf = function (value) {
    for (let i = 0, length = this.length; i < length; i++) {
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
  const cursor = pointers[pointers.indexOf(arr) + 1]
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    let ct = 0
    for (const k in arr) {
      if (ct === cursor) {
        return _php_array_key(k) // 7, not "7"
      }
      ct++
    }
    // Empty
    return null // PHP 8: null past the end
  }
  if (arr.length === 0) {
    return null
  }

  return cursor
}
