function sort (inputArr, sortFlags) {
  //  discuss at: https://locutus.io/php/sort/
  // original by: Kevin van Zonneveld (https://kvz.io)
  //  revised by: Brett Zamir (https://brett-zamir.me)
  // improved by: Brett Zamir (https://brett-zamir.me)
  //      note 1: SORT_STRING (as well as natsort and natcasesort) might also be
  //      note 1: integrated into all of these functions by adapting the code at
  //      note 1: https://sourcefrog.net/projects/natsort/natcompare.js
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
  //   example 1: var $arr = ['Kevin', 'van', 'Zonneveld']
  //   example 1: sort($arr)
  //   example 1: var $result = $arr
  //   returns 1: ['Kevin', 'Zonneveld', 'van']
  //   example 2: ini_set('locutus.sortByReference', true)
  //   example 2: var $fruits = {d: 'lemon', a: 'orange', b: 'banana', c: 'apple'}
  //   example 2: sort($fruits)
  //   example 2: var $result = $fruits
  //   returns 2: {0: 'apple', 1: 'banana', 2: 'lemon', 3: 'orange'}
  //        test: skip-1



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
      // leave sorter undefined, so built-in comparison is used
      break
    case 'SORT_LOCALE_STRING':
      // compare items as strings, based on the current locale
      // (set with i18n_loc_set_default() as of PHP6)
      var loc = $locutus.php.locales[i18nlgd()]

      if (loc && loc.sorting) {
        // if sorting exists on locale object, use it
        // otherwise let sorter be undefined
        // to fallback to built-in behavior
        sorter = loc.sorting
      }
      break
    case 'SORT_NUMERIC':
      // compare items numerically
      sorter = function (a, b) {
        return (a - b)
      }
      break
    case 'SORT_REGULAR':
    default:
      sorter = function (a, b) {
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

  const iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr

  const valArr = []
  for (k in inputArr) {
    // Get key and value arrays
    if (inputArr.hasOwnProperty(k)) {
      valArr.push(inputArr[k])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }

  valArr.sort(sorter)

  for (i = 0; i < valArr.length; i++) {
    // Repopulate the old array
    populateArr[i] = valArr[i]
  }
  return sortByReference || populateArr
}
