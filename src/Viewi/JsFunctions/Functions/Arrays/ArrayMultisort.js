function array_multisort (arr) { 
  var g
  var i
  var j
  var k
  var l
  var sal
  var vkey
  var elIndex
  var lastSorts
  var tmpArray
  var zlast
  var sortFlag = [0]
  var thingsToSort = []
  var nLastSort = []
  var lastSort = []
  var args = arguments
  var flags = {
    'SORT_REGULAR': 16,
    'SORT_NUMERIC': 17,
    'SORT_STRING': 18,
    'SORT_ASC': 32,
    'SORT_DESC': 40
  }
  var sortDuplicator = function (a, b) {
    return nLastSort.shift()
  }
  var sortFunctions = [
    [
      function (a, b) {
        lastSort.push(a > b ? 1 : (a < b ? -1 : 0))
        return a > b ? 1 : (a < b ? -1 : 0)
      },
      function (a, b) {
        lastSort.push(b > a ? 1 : (b < a ? -1 : 0))
        return b > a ? 1 : (b < a ? -1 : 0)
      }
    ],
    [
      function (a, b) {
        lastSort.push(a - b)
        return a - b
      },
      function (a, b) {
        lastSort.push(b - a)
        return b - a
      }
    ],
    [
      function (a, b) {
        lastSort.push((a + '') > (b + '') ? 1 : ((a + '') < (b + '') ? -1 : 0))
        return (a + '') > (b + '') ? 1 : ((a + '') < (b + '') ? -1 : 0)
      },
      function (a, b) {
        lastSort.push((b + '') > (a + '') ? 1 : ((b + '') < (a + '') ? -1 : 0))
        return (b + '') > (a + '') ? 1 : ((b + '') < (a + '') ? -1 : 0)
      }
    ]
  ]
  var sortArrs = [
    []
  ]
  var sortKeys = [
    []
  ]
  if (Object.prototype.toString.call(arr) === '[object Array]') {
    sortArrs[0] = arr
  } else if (arr && typeof arr === 'object') {
    for (i in arr) {
      if (arr.hasOwnProperty(i)) {
        sortKeys[0].push(i)
        sortArrs[0].push(arr[i])
      }
    }
  } else {
    return false
  }
  var arrMainLength = sortArrs[0].length
  var sortComponents = [0, arrMainLength]
  var argl = arguments.length
  for (j = 1; j < argl; j++) {
    if (Object.prototype.toString.call(arguments[j]) === '[object Array]') {
      sortArrs[j] = arguments[j]
      sortFlag[j] = 0
      if (arguments[j].length !== arrMainLength) {
        return false
      }
    } else if (arguments[j] && typeof arguments[j] === 'object') {
      sortKeys[j] = []
      sortArrs[j] = []
      sortFlag[j] = 0
      for (i in arguments[j]) {
        if (arguments[j].hasOwnProperty(i)) {
          sortKeys[j].push(i)
          sortArrs[j].push(arguments[j][i])
        }
      }
      if (sortArrs[j].length !== arrMainLength) {
        return false
      }
    } else if (typeof arguments[j] === 'string') {
      var lFlag = sortFlag.pop()
      if (typeof flags[arguments[j]] === 'undefined' ||
        ((((flags[arguments[j]]) >>> 4) & (lFlag >>> 4)) > 0)) {
        return false
      }
      sortFlag.push(lFlag + flags[arguments[j]])
    } else {
      return false
    }
  }
  for (i = 0; i !== arrMainLength; i++) {
    thingsToSort.push(true)
  }
  for (i in sortArrs) {
    if (sortArrs.hasOwnProperty(i)) {
      lastSorts = []
      tmpArray = []
      elIndex = 0
      nLastSort = []
      lastSort = []
      if (sortComponents.length === 0) {
        if (Object.prototype.toString.call(arguments[i]) === '[object Array]') {
          args[i] = sortArrs[i]
        } else {
          for (k in arguments[i]) {
            if (arguments[i].hasOwnProperty(k)) {
              delete arguments[i][k]
            }
          }
          sal = sortArrs[i].length
          for (j = 0, vkey = 0; j < sal; j++) {
            vkey = sortKeys[i][j]
            args[i][vkey] = sortArrs[i][j]
          }
        }
        sortArrs.splice(i, 1)
        sortKeys.splice(i, 1)
        continue
      }
      var sFunction = sortFunctions[(sortFlag[i] & 3)][((sortFlag[i] & 8) > 0) ? 1 : 0]
      for (l = 0; l !== sortComponents.length; l += 2) {
        tmpArray = sortArrs[i].slice(sortComponents[l], sortComponents[l + 1] + 1)
        tmpArray.sort(sFunction)
        lastSorts[l] = [].concat(lastSort)
        elIndex = sortComponents[l]
        for (g in tmpArray) {
          if (tmpArray.hasOwnProperty(g)) {
            sortArrs[i][elIndex] = tmpArray[g]
            elIndex++
          }
        }
      }
      sFunction = sortDuplicator
      for (j in sortArrs) {
        if (sortArrs.hasOwnProperty(j)) {
          if (sortArrs[j] === sortArrs[i]) {
            continue
          }
          for (l = 0; l !== sortComponents.length; l += 2) {
            tmpArray = sortArrs[j].slice(sortComponents[l], sortComponents[l + 1] + 1)
            nLastSort = [].concat(lastSorts[l])
            tmpArray.sort(sFunction)
            elIndex = sortComponents[l]
            for (g in tmpArray) {
              if (tmpArray.hasOwnProperty(g)) {
                sortArrs[j][elIndex] = tmpArray[g]
                elIndex++
              }
            }
          }
        }
      }
      for (j in sortKeys) {
        if (sortKeys.hasOwnProperty(j)) {
          for (l = 0; l !== sortComponents.length; l += 2) {
            tmpArray = sortKeys[j].slice(sortComponents[l], sortComponents[l + 1] + 1)
            nLastSort = [].concat(lastSorts[l])
            tmpArray.sort(sFunction)
            elIndex = sortComponents[l]
            for (g in tmpArray) {
              if (tmpArray.hasOwnProperty(g)) {
                sortKeys[j][elIndex] = tmpArray[g]
                elIndex++
              }
            }
          }
        }
      }
      zlast = null
      sortComponents = []
      for (j in sortArrs[i]) {
        if (sortArrs[i].hasOwnProperty(j)) {
          if (!thingsToSort[j]) {
            if ((sortComponents.length & 1)) {
              sortComponents.push(j - 1)
            }
            zlast = null
            continue
          }
          if (!(sortComponents.length & 1)) {
            if (zlast !== null) {
              if (sortArrs[i][j] === zlast) {
                sortComponents.push(j - 1)
              } else {
                thingsToSort[j] = false
              }
            }
            zlast = sortArrs[i][j]
          } else {
            if (sortArrs[i][j] !== zlast) {
              sortComponents.push(j - 1)
              zlast = sortArrs[i][j]
            }
          }
        }
      }
      if (sortComponents.length & 1) {
        sortComponents.push(j)
      }
      if (Object.prototype.toString.call(arguments[i]) === '[object Array]') {
        args[i] = sortArrs[i]
      } else {
        for (j in arguments[i]) {
          if (arguments[i].hasOwnProperty(j)) {
            delete arguments[i][j]
          }
        }
        sal = sortArrs[i].length
        for (j = 0, vkey = 0; j < sal; j++) {
          vkey = sortKeys[i][j]
          args[i][vkey] = sortArrs[i][j]
        }
      }
      sortArrs.splice(i, 1)
      sortKeys.splice(i, 1)
    }
  }
  return true
}
