function is_numeric (mixedVar) { 
  var whitespace = [
    ' ',
    '\n',
    '\r',
    '\t',
    '\f',
    '\x0b',
    '\xa0',
    '\u2000',
    '\u2001',
    '\u2002',
    '\u2003',
    '\u2004',
    '\u2005',
    '\u2006',
    '\u2007',
    '\u2008',
    '\u2009',
    '\u200a',
    '\u200b',
    '\u2028',
    '\u2029',
    '\u3000'
  ].join('')
  return (typeof mixedVar === 'number' ||
    (typeof mixedVar === 'string' &&
    whitespace.indexOf(mixedVar.slice(-1)) === -1)) &&
    mixedVar !== '' &&
    !isNaN(mixedVar)
}
