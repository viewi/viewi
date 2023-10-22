function var_dump () { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/var_dump/
  // original by: Brett Zamir (https://brett-zamir.me)
  // improved by: Zahlii
  // improved by: Brett Zamir (https://brett-zamir.me)
  //      note 1: For returning a string, use var_export() with the second argument set to true
  //        test: skip-all
  //   example 1: var_dump(1)
  //   returns 1: 'int(1)'


  let output = ''
  const padChar = ' '
  const padVal = 4
  let lgth = 0
  let i = 0

  const _getFuncName = function (fn) {
    const name = (/\W*function\s+([\w$]+)\s*\(/)
      .exec(fn)
    if (!name) {
      return '(Anonymous)'
    }
    return name[1]
  }

  const _repeatChar = function (len, padChar) {
    let str = ''
    for (let i = 0; i < len; i++) {
      str += padChar
    }
    return str
  }
  const _getInnerVal = function (val, thickPad) {
    let ret = ''
    if (val === null) {
      ret = 'NULL'
    } else if (typeof val === 'boolean') {
      ret = 'bool(' + val + ')'
    } else if (typeof val === 'string') {
      ret = 'string(' + val.length + ') "' + val + '"'
    } else if (typeof val === 'number') {
      if (parseFloat(val) === parseInt(val, 10)) {
        ret = 'int(' + val + ')'
      } else {
        ret = 'float(' + val + ')'
      }
    } else if (typeof val === 'undefined') {
      // The remaining are not PHP behavior because these values
      // only exist in this exact form in JavaScript
      ret = 'undefined'
    } else if (typeof val === 'function') {
      const funcLines = val.toString()
        .split('\n')
      ret = ''
      for (let i = 0, fll = funcLines.length; i < fll; i++) {
        ret += (i !== 0 ? '\n' + thickPad : '') + funcLines[i]
      }
    } else if (val instanceof Date) {
      ret = 'Date(' + val + ')'
    } else if (val instanceof RegExp) {
      ret = 'RegExp(' + val + ')'
    } else if (val.nodeName) {
      // Different than PHP's DOMElement
      switch (val.nodeType) {
        case 1:
          if (typeof val.namespaceURI === 'undefined' ||
            val.namespaceURI === 'https://www.w3.org/1999/xhtml') {
          // Undefined namespace could be plain XML, but namespaceURI not widely supported
            ret = 'HTMLElement("' + val.nodeName + '")'
          } else {
            ret = 'XML Element("' + val.nodeName + '")'
          }
          break
        case 2:
          ret = 'ATTRIBUTE_NODE(' + val.nodeName + ')'
          break
        case 3:
          ret = 'TEXT_NODE(' + val.nodeValue + ')'
          break
        case 4:
          ret = 'CDATA_SECTION_NODE(' + val.nodeValue + ')'
          break
        case 5:
          ret = 'ENTITY_REFERENCE_NODE'
          break
        case 6:
          ret = 'ENTITY_NODE'
          break
        case 7:
          ret = 'PROCESSING_INSTRUCTION_NODE(' + val.nodeName + ':' + val.nodeValue + ')'
          break
        case 8:
          ret = 'COMMENT_NODE(' + val.nodeValue + ')'
          break
        case 9:
          ret = 'DOCUMENT_NODE'
          break
        case 10:
          ret = 'DOCUMENT_TYPE_NODE'
          break
        case 11:
          ret = 'DOCUMENT_FRAGMENT_NODE'
          break
        case 12:
          ret = 'NOTATION_NODE'
          break
      }
    }
    return ret
  }

  var _formatArray = function (obj, curDepth, padVal, padChar) {
    if (curDepth > 0) {
      curDepth++
    }

    const basePad = _repeatChar(padVal * (curDepth - 1), padChar)
    const thickPad = _repeatChar(padVal * (curDepth + 1), padChar)
    let str = ''
    let val = ''

    if (typeof obj === 'object' && obj !== null) {
      if (obj.constructor && _getFuncName(obj.constructor) === 'LOCUTUS_Resource') {
        return obj.var_dump()
      }
      lgth = 0
      for (const someProp in obj) {
        if (obj.hasOwnProperty(someProp)) {
          lgth++
        }
      }
      str += 'array(' + lgth + ') {\n'
      for (const key in obj) {
        const objVal = obj[key]
        if (typeof objVal === 'object' &&
          objVal !== null &&
          !(objVal instanceof Date) &&
          !(objVal instanceof RegExp) &&
          !objVal.nodeName) {
          str += thickPad
          str += '['
          str += key
          str += '] =>\n'
          str += thickPad
          str += _formatArray(objVal, curDepth + 1, padVal, padChar)
        } else {
          val = _getInnerVal(objVal, thickPad)
          str += thickPad
          str += '['
          str += key
          str += '] =>\n'
          str += thickPad
          str += val
          str += '\n'
        }
      }
      str += basePad + '}\n'
    } else {
      str = _getInnerVal(obj, thickPad)
    }
    return str
  }

  output = _formatArray(arguments[0], 0, padVal, padChar)
  for (i = 1; i < arguments.length; i++) {
    output += '\n' + _formatArray(arguments[i], 0, padVal, padChar)
  }

  echo(output)

  // Not how PHP does it, but helps us test:
  return output
}
