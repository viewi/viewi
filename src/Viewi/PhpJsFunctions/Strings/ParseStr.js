function parse_str (str, array) { // eslint-disable-line camelcase
  //       discuss at: https://locutus.io/php/parse_str/
  //      original by: Cagri Ekin
  //      improved by: Michael White (https://getsprink.com)
  //      improved by: Jack
  //      improved by: Brett Zamir (https://brett-zamir.me)
  //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
  //      bugfixed by: Brett Zamir (https://brett-zamir.me)
  //      bugfixed by: stag019
  //      bugfixed by: Brett Zamir (https://brett-zamir.me)
  //      bugfixed by: MIO_KODUKI (https://mio-koduki.blogspot.com/)
  // reimplemented by: stag019
  //         input by: Dreamer
  //         input by: Zaide (https://zaidesthings.com/)
  //         input by: David Pesta (https://davidpesta.com/)
  //         input by: jeicquest
  //      bugfixed by: Rafał Kukawski
  //           note 1: When no argument is specified, will put variables in global scope.
  //           note 1: When a particular argument has been passed, and the
  //           note 1: returned value is different parse_str of PHP.
  //           note 1: For example, a=b=c&d====c
  //        example 1: var $arr = {}
  //        example 1: parse_str('first=foo&second=bar', $arr)
  //        example 1: var $result = $arr
  //        returns 1: { first: 'foo', second: 'bar' }
  //        example 2: var $arr = {}
  //        example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', $arr)
  //        example 2: var $result = $arr
  //        returns 2: { str_a: "Jack and Jill didn't see the well." }
  //        example 3: var $abc = {3:'a'}
  //        example 3: parse_str('a[b]["c"]=def&a[q]=t+5', $abc)
  //        example 3: var $result = $abc
  //        returns 3: {"3":"a","a":{"b":{"c":"def"},"q":"t 5"}}
  //        example 4: var $arr = {}
  //        example 4: parse_str('a[][]=value', $arr)
  //        example 4: var $result = $arr
  //        returns 4: {"a":{"0":{"0":"value"}}}
  //        example 5: var $arr = {}
  //        example 5: parse_str('a=1&a[]=2', $arr)
  //        example 5: var $result = $arr
  //        returns 5: {"a":{"0":"2"}}

  const strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&')
  const sal = strArr.length
  let i
  let j
  let ct
  let p
  let lastObj
  let obj
  let chr
  let tmp
  let key
  let value
  let postLeftBracketPos
  let keys
  let keysLen

  const _fixStr = function (str) {
    return decodeURIComponent(str.replace(/\+/g, '%20'))
  }

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
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
          // Insert new dimension
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

        // if primitive value, replace with object
        if (Object(obj[key]) !== obj[key]) {
          obj[key] = {}
        }

        obj = obj[key]
      }

      lastObj[key] = value
    }
  }
}
