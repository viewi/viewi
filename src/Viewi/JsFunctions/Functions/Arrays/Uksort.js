function uksort (inputArr, sorter) {
  var tmpArr = {}
  var keys = []
  var i = 0
  var k = ''
  var sortByReference = false
  var populateArr = {}
  if (typeof sorter === 'string') {
    sorter = this.window[sorter]
  }
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      keys.push(k)
    }
  }
  try {
    if (sorter) {
      keys.sort(sorter)
    } else {
      keys.sort()
    }
  } catch (e) {
    return false
  }
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
