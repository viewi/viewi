function ltrim (str, charlist) {
  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([[\]().?/*{}+$^:])/g, '$1')
  var re = new RegExp('^[' + charlist + ']+', 'g')
  return (str + '')
    .replace(re, '')
}
