function uasort (inputArr, sorter) {
  var valArr = []
  var k = ''
  var i = 0
  var sortByReference = false
  var populateArr = {}
  if (typeof sorter === 'string') {
    sorter = this[sorter]
  } else if (Object.prototype.toString.call(sorter) === '[object Array]') {
    sorter = this[sorter[0]][sorter[1]]
  }
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
    return sorter(a[1], b[1])
  })
  for (i = 0; i < valArr.length; i++) {
    populateArr[valArr[i][0]] = valArr[i][1]
  }
  return sortByReference || populateArr
}
