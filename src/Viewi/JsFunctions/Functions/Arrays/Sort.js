function sort (inputArr, sortFlags) {
  var i18nlgd = window.i18n_loc_get_default
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
      break
    case 'SORT_LOCALE_STRING':
      var loc = $locutus.php.locales[i18nlgd()]
      if (loc && loc.sorting) {
        sorter = loc.sorting
      }
      break
    case 'SORT_NUMERIC':
      sorter = function (a, b) {
        return (a - b)
      }
      break
    case 'SORT_REGULAR':
    default:
      sorter = function (a, b) {
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
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr
  var valArr = []
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      valArr.push(inputArr[k])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }
  valArr.sort(sorter)
  for (i = 0; i < valArr.length; i++) {
    populateArr[i] = valArr[i]
  }
  return sortByReference || populateArr
}
