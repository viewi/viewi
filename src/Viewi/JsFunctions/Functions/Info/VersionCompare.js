function version_compare (v1, v2, operator) { 
  var i
  var x
  var compare = 0
  var vm = {
    'dev': -6,
    'alpha': -5,
    'a': -5,
    'beta': -4,
    'b': -4,
    'RC': -3,
    'rc': -3,
    '#': -2,
    'p': 1,
    'pl': 1
  }
  var _prepVersion = function (v) {
    v = ('' + v).replace(/[_\-+]/g, '.')
    v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
    return (!v.length ? [-8] : v.split('.'))
  }
  var _numVersion = function (v) {
    return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
  }
  v1 = _prepVersion(v1)
  v2 = _prepVersion(v2)
  x = Math.max(v1.length, v2.length)
  for (i = 0; i < x; i++) {
    if (v1[i] === v2[i]) {
      continue
    }
    v1[i] = _numVersion(v1[i])
    v2[i] = _numVersion(v2[i])
    if (v1[i] < v2[i]) {
      compare = -1
      break
    } else if (v1[i] > v2[i]) {
      compare = 1
      break
    }
  }
  if (!operator) {
    return compare
  }
  switch (operator) {
    case '>':
    case 'gt':
      return (compare > 0)
    case '>=':
    case 'ge':
      return (compare >= 0)
    case '<=':
    case 'le':
      return (compare <= 0)
    case '===':
    case '=':
    case 'eq':
      return (compare === 0)
    case '<>':
    case '!==':
    case 'ne':
      return (compare !== 0)
    case '':
    case '<':
    case 'lt':
      return (compare < 0)
    default:
      return null
  }
}
