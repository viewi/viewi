function max () {
  //  discuss at: https://locutus.io/php/max/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //  revised by: Onno Marsman (https://twitter.com/onnomarsman)
  // improved by: Jack
  //      note 1: Long code cause we're aiming for maximum PHP compatibility
  //   example 1: max(1, 3, 5, 6, 7)
  //   returns 1: 7
  //   example 2: max([2, 4, 5])
  //   returns 2: 5
  //   example 3: max(0, 'hello')
  //   returns 3: 0
  //   example 4: max('hello', 0)
  //   returns 4: 'hello'
  //   example 5: max(-1, 'hello')
  //   returns 5: 'hello'
  //   example 6: max([2, 4, 8], [2, 5, 7])
  //   returns 6: [2, 5, 7]

  let ar
  let retVal
  let i = 0
  let n = 0
  const argv = arguments
  const argc = argv.length
  const _obj2Array = function (obj) {
    if (Object.prototype.toString.call(obj) === '[object Array]') {
      return obj
    } else {
      const ar = []
      for (const i in obj) {
        if (obj.hasOwnProperty(i)) {
          ar.push(obj[i])
        }
      }
      return ar
    }
  }
  var _compare = function (current, next) {
    let i = 0
    let n = 0
    let tmp = 0
    let nl = 0
    let cl = 0

    if (current === next) {
      return 0
    } else if (typeof current === 'object') {
      if (typeof next === 'object') {
        current = _obj2Array(current)
        next = _obj2Array(next)
        cl = current.length
        nl = next.length
        if (nl > cl) {
          return 1
        } else if (nl < cl) {
          return -1
        }
        for (i = 0, n = cl; i < n; ++i) {
          tmp = _compare(current[i], next[i])
          if (tmp === 1) {
            return 1
          } else if (tmp === -1) {
            return -1
          }
        }
        return 0
      }
      return -1
    } else if (typeof next === 'object') {
      return 1
    } else if (isNaN(next) && !isNaN(current)) {
      if (current === 0) {
        return 0
      }
      return (current < 0 ? 1 : -1)
    } else if (isNaN(current) && !isNaN(next)) {
      if (next === 0) {
        return 0
      }
      return (next > 0 ? 1 : -1)
    }

    if (next === current) {
      return 0
    }

    return (next > current ? 1 : -1)
  }

  if (argc === 0) {
    throw new Error('At least one value should be passed to max()')
  } else if (argc === 1) {
    if (typeof argv[0] === 'object') {
      ar = _obj2Array(argv[0])
    } else {
      throw new Error('Wrong parameter count for max()')
    }
    if (ar.length === 0) {
      throw new Error('Array must contain at least one element for max()')
    }
  } else {
    ar = argv
  }

  retVal = ar[0]
  for (i = 1, n = ar.length; i < n; ++i) {
    if (_compare(retVal, ar[i]) === 1) {
      retVal = ar[i]
    }
  }

  return retVal
}
