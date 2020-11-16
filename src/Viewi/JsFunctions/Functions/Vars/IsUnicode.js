function is_unicode (vr) { 
  if (typeof vr !== 'string') {
    return false
  }
  var arr = []
  var highSurrogate = '[\uD800-\uDBFF]'
  var lowSurrogate = '[\uDC00-\uDFFF]'
  var highSurrogateBeforeAny = new RegExp(highSurrogate + '([\\s\\S])', 'g')
  var lowSurrogateAfterAny = new RegExp('([\\s\\S])' + lowSurrogate, 'g')
  var singleLowSurrogate = new RegExp('^' + lowSurrogate + '$')
  var singleHighSurrogate = new RegExp('^' + highSurrogate + '$')
  while ((arr = highSurrogateBeforeAny.exec(vr)) !== null) {
    if (!arr[1] || !arr[1].match(singleLowSurrogate)) {
      return false
    }
  }
  while ((arr = lowSurrogateAfterAny.exec(vr)) !== null) {
    if (!arr[1] || !arr[1].match(singleHighSurrogate)) {
      return false
    }
  }
  return true
}
