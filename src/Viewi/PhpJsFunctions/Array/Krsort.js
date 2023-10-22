function krsort (inputArr, sortFlags) {
  //  discuss at: https://locutus.io/php/krsort/
  // original by: GeekFG (https://geekfg.blogspot.com)
  // improved by: Kevin van Zonneveld (https://kvz.io)
  // improved by: Brett Zamir (https://brett-zamir.me)
  // bugfixed by: pseudaria (https://github.com/pseudaria)
  //      note 1: The examples are correct, this is a new way
  //      note 1: This function deviates from PHP in returning a copy of the array instead
  //      note 1: of acting by reference and returning true; this was necessary because
  //      note 1: IE does not allow deleting and re-adding of properties without caching
  //      note 1: of property position; you can set the ini of "locutus.sortByReference" to true to
  //      note 1: get the PHP behavior, but use this only if you are in an environment
  //      note 1: such as Firefox extensions where for-in iteration order is fixed and true
  //      note 1: property deletion is supported. Note that we intend to implement the PHP
  //      note 1: behavior by default if IE ever does allow it; only gives shallow copy since
  //      note 1: is by reference in PHP anyways
  //      note 1: Since JS objects' keys are always strings, and (the
  //      note 1: default) SORT_REGULAR flag distinguishes by key type,
  //      note 1: if the content is a numeric string, we treat the
  //      note 1: "original type" as numeric.
  //   example 1: var $data = {d: 'lemon', a: 'orange', b: 'banana', c: 'apple'}
  //   example 1: krsort($data)
  //   example 1: var $result = $data
  //   returns 1: {d: 'lemon', c: 'apple', b: 'banana', a: 'orange'}
  //   example 2: ini_set('locutus.sortByReference', true)
  //   example 2: var $data = {2: 'van', 3: 'Zonneveld', 1: 'Kevin'}
  //   example 2: krsort($data)
  //   example 2: var $result = $data
  //   returns 2: {3: 'Zonneveld', 2: 'van', 1: 'Kevin'}




  const tmpArr = {}
  const keys = []
  let sorter
  let i
  let k
  let sortByReference = false
  let populateArr = {}

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.locales = $locutus.php.locales || {}

  switch (sortFlags) {
    case 'SORT_STRING':
      // compare items as strings
      sorter = function (a, b) {
        return strnatcmp(b, a)
      }
      break
    case 'SORT_LOCALE_STRING':
      // compare items as strings, based on the current locale
      // (set with i18n_loc_set_default() as of PHP6)
      var loc = i18nlgd()
      sorter = $locutus.locales[loc].sorting
      break
    case 'SORT_NUMERIC':
      // compare items numerically
      sorter = function (a, b) {
        return (b - a)
      }
      break
    case 'SORT_REGULAR':
    default:
      // compare items normally (don't change types)
      sorter = function (b, a) {
        const aFloat = parseFloat(a)
        const bFloat = parseFloat(b)
        const aNumeric = aFloat + '' === a
        const bNumeric = bFloat + '' === b
        if (aNumeric && bNumeric) {
          return aFloat > bFloat ? 1 : aFloat < bFloat ? -1 : 0
        } else if (aNumeric && !bNumeric) {
          return 1
        } else if (!aNumeric && bNumeric) {
          return -1
        }
        return a > b ? 1 : a < b ? -1 : 0
      }
      break
  }

  // Make a list of key names
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      keys.push(k)
    }
  }
  keys.sort(sorter)

  const iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr

  // Rebuild array with sorted key names
  for (i = 0; i < keys.length; i++) {
    k = keys[i]
    tmpArr[k] = inputArr[k]
    if (sortByReference) {
      delete inputArr[k]
    }
  }
  for (i in tmpArr) {
    if (tmpArr.hasOwnProperty(i)) {
      populateArr[i] = tmpArr[i]
    }
  }

  return sortByReference || populateArr
}
