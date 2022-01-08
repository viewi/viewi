function var_export (mixedExpression, boolReturn) { 
  var echo = console.log
  var retstr = ''
  var iret = ''
  var value
  var cnt = 0
  var x = []
  var i = 0
  var funcParts = []
  var idtLevel = arguments[2] || 2
  var innerIndent = ''
  var outerIndent = ''
  var getFuncName = function (fn) {
    var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
    if (!name) {
      return '(Anonymous)'
    }
    return name[1]
  }
  var _isNormalInteger = function (string) {
    var number = Math.floor(Number(string))
    return number !== Infinity && String(number) === string && number >= 0
  }
  var _makeIndent = function (idtLevel) {
    return (new Array(idtLevel + 1))
      .join(' ')
  }
  var __getType = function (inp) {
    var i = 0
    var match
    var types
    var cons
    var type = typeof inp
    if (type === 'object' && (inp && inp.constructor) &&
      getFuncName(inp.constructor) === 'LOCUTUS_Resource') {
      return 'resource'
    }
    if (type === 'function') {
      return 'function'
    }
    if (type === 'object' && !inp) {
      return 'null'
    }
    if (type === 'object') {
      if (!inp.constructor) {
        return 'object'
      }
      cons = inp.constructor.toString()
      match = cons.match(/(\w+)\(/)
      if (match) {
        cons = match[1].toLowerCase()
      }
      types = ['boolean', 'number', 'string', 'array']
      for (i = 0; i < types.length; i++) {
        if (cons === types[i]) {
          type = types[i]
          break
        }
      }
    }
    return type
  }
  var type = __getType(mixedExpression)
  if (type === null) {
    retstr = 'NULL'
  } else if (type === 'array' || type === 'object') {
    outerIndent = _makeIndent(idtLevel - 2)
    innerIndent = _makeIndent(idtLevel)
    for (i in mixedExpression) {
      value = ' '
      var subtype = __getType(mixedExpression[i])
      if (subtype === 'array' || subtype === 'object') {
        value = '\n'
      }
      value += var_export(mixedExpression[i], 1, idtLevel + 2)
      i = _isNormalInteger(i) ? i : `'${i}'`
      x[cnt++] = innerIndent + i + ' =>' + value
    }
    if (x.length > 0) {
      iret = x.join(',\n') + ',\n'
    }
    retstr = outerIndent + 'array (\n' + iret + outerIndent + ')'
  } else if (type === 'function') {
    funcParts = mixedExpression.toString().match(/function .*?\((.*?)\) \{([\s\S]*)\}/)
    retstr = "create_function ('" + funcParts[1] + "', '" +
      funcParts[2].replace(new RegExp("'", 'g'), "\\'") + "')"
  } else if (type === 'resource') {
    retstr = 'NULL'
  } else {
    retstr = typeof mixedExpression !== 'string' ? mixedExpression
      : "'" + mixedExpression.replace(/(["'])/g, '\\$1').replace(/\0/g, '\\0') + "'"
  }
  if (!boolReturn) {
    echo(retstr)
    return null
  }
  return retstr
}
