function natsort (inputArr) {
  var strnatcmp = window.strnatcmp
  var valArr = []
  var k
  var i
  var sortByReference = false
  var populateArr = {}
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      valArr.push([k, inputArr[k]])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }
  valArr.sort(function (a, b) {
    return strnatcmp(a[1], b[1])
  })
  for (i = 0; i < valArr.length; i++) {
    populateArr[valArr[i][0]] = valArr[i][1]
  }
  return sortByReference || populateArr
}
