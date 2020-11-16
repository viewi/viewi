function nl2br (str, isXhtml) {
  if (typeof str === 'undefined' || str === null) {
    return ''
  }
  var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br ' + '/>' : '<br>'
  return (str + '')
    .replace(/(\r\n|\n\r|\r|\n)/g, breakTag + '$1')
}
