function usort (inputArr, sorter) {
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
      valArr.push(inputArr[k])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }
  try {
    valArr.sort(sorter)
  } catch (e) {
    return false
  }
  for (i = 0; i < valArr.length; i++) {
    populateArr[i] = valArr[i]
  }
  return sortByReference || populateArr
}
