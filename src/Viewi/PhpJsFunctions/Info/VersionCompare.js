function version_compare(v1, v2, operator) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.version-compare.php
  // PHP's algorithm: '-', '_', '+' become '.', a '.' goes between digits and letters, then part by
  // part: numbers numerically, words by rank dev < alpha = a < beta = b < RC = rc < (number) <
  // pl = p. A longer version wins when its next part is a number ('1.0.0' > '1.0').
  const canon = function (v) {
    return _phpCastString(v)
      .replace(/[-_+]/g, '.')
      .replace(/(\d)([^\d.])/g, '$1.$2')
      .replace(/([^\d.])(\d)/g, '$1.$2')
      .split('.')
      .filter(function (p) {
        return p !== ''
      })
  }
  const forms = [['dev', 0], ['alpha', 1], ['a', 1], ['beta', 2], ['b', 2], ['RC', 3], ['rc', 3], ['#', 4], ['pl', 5], ['p', 5]]
  const rank = function (part) {
    for (const [name, order] of forms) {
      if (part.startsWith(name)) {
        return order
      }
    }
    return -1
  }
  const sign = function (n) {
    return n < 0 ? -1 : (n > 0 ? 1 : 0)
  }
  const isNum = function (p) {
    return /^\d/.test(p)
  }
  const compare = function (a, b) {
    const n = Math.min(a.length, b.length)
    for (let i = 0; i < n; i++) {
      let r
      if (isNum(a[i]) && isNum(b[i])) {
        r = sign(parseInt(a[i], 10) - parseInt(b[i], 10))
      } else {
        r = sign(rank(isNum(a[i]) ? '#' : a[i]) - rank(isNum(b[i]) ? '#' : b[i]))
      }
      if (r !== 0) {
        return r
      }
    }
    if (a.length > n) {
      return isNum(a[n]) ? 1 : compare(a.slice(n), ['#'])
    }
    if (b.length > n) {
      return isNum(b[n]) ? -1 : compare(['#'], b.slice(n))
    }
    return 0
  }
  const result = compare(canon(v1), canon(v2))
  switch (operator) {
    case undefined:
    case null:
      return result
    case '<':
    case 'lt':
      return result < 0
    case '<=':
    case 'le':
      return result <= 0
    case '>':
    case 'gt':
      return result > 0
    case '>=':
    case 'ge':
      return result >= 0
    case '==':
    case 'eq':
      return result === 0
    case '!=':
    case '<>':
    case 'ne':
      return result !== 0
  }
  throw new Error('version_compare(): Argument #3 ($operator) must be a valid comparison operator')
}
