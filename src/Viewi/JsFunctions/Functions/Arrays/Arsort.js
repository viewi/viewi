function arsort (inputArr, sortFlags) {
  var i18lgd = window.i18n_loc_get_default
  var strnatcmp = window.strnatcmp
  var valArr = []
  var valArrLen = 0
  var k
  var i
  var sorter
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
      var loc = i18lgd()
      sorter = $locutus.php.locales[loc].sorting
      break
    case 'SORT_NUMERIC':
      sorter = function (a, b) {
        return (a - b)
      }
      break
    case 'SORT_REGULAR':
      break
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
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      valArr.push([k, inputArr[k]])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }
  valArr.sort(function (a, b) {
    return sorter(a[1], b[1])
  })
  for (i = 0, valArrLen = valArr.length; i < valArrLen; i++) {
    populateArr[valArr[i][0]] = valArr[i][1]
    if (sortByReference) {
      inputArr[valArr[i][0]] = valArr[i][1]
    }
  }
  return sortByReference || populateArr
}
