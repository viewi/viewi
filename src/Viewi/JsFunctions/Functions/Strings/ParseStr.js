function parse_str (str, array) { 
  var strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&')
  var sal = strArr.length
  var i
  var j
  var ct
  var p
  var lastObj
  var obj
  var chr
  var tmp
  var key
  var value
  var postLeftBracketPos
  var keys
  var keysLen
  var _fixStr = function (str) {
    return decodeURIComponent(str.replace(/\+/g, '%20'))
  }
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  if (!array) {
    array = $global
  }
  for (i = 0; i < sal; i++) {
    tmp = strArr[i].split('=')
    key = _fixStr(tmp[0])
    value = (tmp.length < 2) ? '' : _fixStr(tmp[1])
    if (key.includes('__proto__') || key.includes('constructor') || key.includes('prototype')) {
      break
    }
    while (key.charAt(0) === ' ') {
      key = key.slice(1)
    }
    if (key.indexOf('\x00') > -1) {
      key = key.slice(0, key.indexOf('\x00'))
    }
    if (key && key.charAt(0) !== '[') {
      keys = []
      postLeftBracketPos = 0
      for (j = 0; j < key.length; j++) {
        if (key.charAt(j) === '[' && !postLeftBracketPos) {
          postLeftBracketPos = j + 1
        } else if (key.charAt(j) === ']') {
          if (postLeftBracketPos) {
            if (!keys.length) {
              keys.push(key.slice(0, postLeftBracketPos - 1))
            }
            keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos))
            postLeftBracketPos = 0
            if (key.charAt(j + 1) !== '[') {
              break
            }
          }
        }
      }
      if (!keys.length) {
        keys = [key]
      }
      for (j = 0; j < keys[0].length; j++) {
        chr = keys[0].charAt(j)
        if (chr === ' ' || chr === '.' || chr === '[') {
          keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1)
        }
        if (chr === '[') {
          break
        }
      }
      obj = array
      for (j = 0, keysLen = keys.length; j < keysLen; j++) {
        key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '')
        lastObj = obj
        if ((key === '' || key === ' ') && j !== 0) {
          ct = -1
          for (p in obj) {
            if (obj.hasOwnProperty(p)) {
              if (+p > ct && p.match(/^\d+$/g)) {
                ct = +p
              }
            }
          }
          key = ct + 1
        }
        if (Object(obj[key]) !== obj[key]) {
          obj[key] = {}
        }
        obj = obj[key]
      }
      lastObj[key] = value
    }
  }
}
