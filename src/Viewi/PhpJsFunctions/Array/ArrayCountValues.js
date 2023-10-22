function array_count_values (array) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_count_values/
  // original by: Ates Goral (https://magnetiq.com)
  // improved by: Michael White (https://getsprink.com)
  // improved by: Kevin van Zonneveld (https://kvz.io)
  //    input by: sankai
  //    input by: Shingo
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //   example 1: array_count_values([ 3, 5, 3, "foo", "bar", "foo" ])
  //   returns 1: {3:2, 5:1, "foo":2, "bar":1}
  //   example 2: array_count_values({ p1: 3, p2: 5, p3: 3, p4: "foo", p5: "bar", p6: "foo" })
  //   returns 2: {3:2, 5:1, "foo":2, "bar":1}
  //   example 3: array_count_values([ true, 4.2, 42, "fubar" ])
  //   returns 3: {42:1, "fubar":1}

  const tmpArr = {}
  let key = ''
  let t = ''

  const _getType = function (obj) {
    // Objects are php associative arrays.
    let t = typeof obj
    t = t.toLowerCase()
    if (t === 'object') {
      t = 'array'
    }
    return t
  }

  const _countValue = function (tmpArr, value) {
    if (typeof value === 'number') {
      if (Math.floor(value) !== value) {
        return
      }
    } else if (typeof value !== 'string') {
      return
    }

    if (value in tmpArr && tmpArr.hasOwnProperty(value)) {
      ++tmpArr[value]
    } else {
      tmpArr[value] = 1
    }
  }

  t = _getType(array)
  if (t === 'array') {
    for (key in array) {
      if (array.hasOwnProperty(key)) {
        _countValue.call(this, tmpArr, array[key])
      }
    }
  }

  return tmpArr
}
