function shuffle (inputArr) {
  var valArr = []
  var k = ''
  var i = 0
  var sortByReference = false
  var populateArr = []
  for (k in inputArr) {
    if (inputArr.hasOwnProperty(k)) {
      valArr.push(inputArr[k])
      if (sortByReference) {
        delete inputArr[k]
      }
    }
  }
  valArr.sort(function () {
    return 0.5 - Math.random()
  })
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.sortByReference') : undefined) || 'on'
  sortByReference = iniVal === 'on'
  populateArr = sortByReference ? inputArr : populateArr
  for (i = 0; i < valArr.length; i++) {
    populateArr[i] = valArr[i]
  }
  return sortByReference || populateArr
}
