function htmlentities (string, quoteStyle, charset, doubleEncode) {
  var getHtmlTranslationTable = require('../strings/get_html_translation_table')
  var hashMap = getHtmlTranslationTable('HTML_ENTITIES', quoteStyle)
  string = string === null ? '' : string + ''
  if (!hashMap) {
    return false
  }
  if (quoteStyle && quoteStyle === 'ENT_QUOTES') {
    hashMap["'"] = '&#039;'
  }
  doubleEncode = doubleEncode === null || !!doubleEncode
  var regex = new RegExp('&(?:#\\d+|#x[\\da-f]+|[a-zA-Z][\\da-z]*);|[' +
    Object.keys(hashMap)
    .join('')
    .replace(/([()[\]{}\-.*+?^$|/\\])/g, '\\$1') + ']',
    'g')
  return string.replace(regex, function (ent) {
    if (ent.length > 1) {
      return doubleEncode ? hashMap['&'] + ent.substr(1) : ent
    }
    return hashMap[ent]
  })
}
