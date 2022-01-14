function strip_tags (input, allowed) { 
  var _phpCastString = window._phpCastString
  allowed = (((allowed || '') + '').toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('')
  var tags = /<\/?([a-z0-9]*)\b[^>]*>?/gi
  var commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi
  var after = _phpCastString(input)
  after = (after.substring(after.length - 1) === '<') ? after.substring(0, after.length - 1) : after
  while (true) {
    var before = after
    after = before.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
      return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : ''
    })
    if (before === after) {
      return after
    }
  }
}
