function krsort (inputArr, sortFlags) {
  var i18nlgd = window.i18n_loc_get_default
  var strnatcmp = window.strnatcmp
  var tmpArr = {}
  var keys = []
  var sorter
  var i
  var k
  var sortByReference = false
  var populateArr = {}
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.locales = $locutus.php.locales || {}
  switch (sortFlags) {
    case 'SORT_STRING':
      sorter = function (a, b) {
        return strnatcmp(b, a)
      }
      break
    case 'SORT_LOCALE_STRING':
      var loc = i18nlgd()
      sorter = $locutus.locales[loc].sorting
      break
    case 'SORT_NUMERIC':
      sorter = function (a, b) {
        return (b - a)
      }
      break
    case 'SORT_REGULAR':
    default:
      sorter = function (b, a) {
        var aFloat = parseFloat(a)
        var bFloat = parseFloat(b)
        var aNumeric = aFloat + '' === a
        var bNumeric = bFloat + '' === b
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
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      keys.push(k)
    }
  }
  keys.sort(sorter)
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr
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
