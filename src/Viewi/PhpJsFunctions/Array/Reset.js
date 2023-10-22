function reset (arr) {
  //  discuss at: https://locutus.io/php/reset/
  // original by: Kevin van Zonneveld (https://kvz.io)
  // bugfixed by: Legaev Andrey
  //  revised by: Brett Zamir (https://brett-zamir.me)
  //      note 1: Uses global: locutus to store the array pointer
  //   example 1: reset({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
  //   returns 1: 'Kevin'

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
  const arrpos = pointers.indexOf(arr)
  if (Object.prototype.toString.call(arr) !== '[object Array]') {
    for (const k in arr) {
      if (pointers.indexOf(arr) === -1) {
        pointers.push(arr, 0)
      } else {
        pointers[arrpos + 1] = 0
      }
      return arr[k]
    }
    // Empty
    return false
  }
  if (arr.length === 0) {
    return false
  }
  pointers[arrpos + 1] = 0
  return arr[pointers[arrpos + 1]]
}
