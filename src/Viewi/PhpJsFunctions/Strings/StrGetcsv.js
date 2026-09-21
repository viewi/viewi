function str_getcsv(input, separator, enclosure, escape) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.str-getcsv.php
  // One CSV line into fields. A doubled enclosure is a literal quote; the escape character
  // (default \) keeps the next character inside the field, both kept in the output as PHP does.
  // An empty line gives [null], as PHP.
  input = _phpCastString(input)
  separator = separator === undefined ? ',' : separator
  enclosure = enclosure === undefined ? '"' : enclosure
  escape = escape === undefined ? '\\' : escape
  if (input === '') {
    return [null]
  }
  const fields = []
  let field = ''
  let i = 0
  let quoted = false
  let started = false
  while (i < input.length) {
    const ch = input[i]
    if (quoted) {
      if (escape !== '' && ch === escape && i + 1 < input.length) {
        field += ch + input[i + 1]
        i += 2
        continue
      }
      if (ch === enclosure) {
        if (input[i + 1] === enclosure) {
          field += enclosure
          i += 2
          continue
        }
        quoted = false
        i++
        continue
      }
      field += ch
      i++
      continue
    }
    if (ch === separator) {
      fields.push(field)
      field = ''
      started = false
      i++
      continue
    }
    if (ch === enclosure && !started && field.trim() === '') {
      quoted = true
      started = true
      field = ''
      i++
      continue
    }
    field += ch
    started = true
    i++
  }
  fields.push(field)
  return fields
}
