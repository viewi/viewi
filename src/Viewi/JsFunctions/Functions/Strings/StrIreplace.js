function str_ireplace (search, replace, subject, countObj) { 
  var i = 0
  var j = 0
  var temp = ''
  var repl = ''
  var sl = 0
  var fl = 0
  var f = ''
  var r = ''
  var s = ''
  var ra = ''
  var otemp = ''
  var oi = ''
  var ofjl = ''
  var os = subject
  var osa = Object.prototype.toString.call(os) === '[object Array]'
  if (typeof (search) === 'object') {
    temp = search
    search = []
    for (i = 0; i < temp.length; i += 1) {
      search[i] = temp[i].toLowerCase()
    }
  } else {
    search = search.toLowerCase()
  }
  if (typeof (subject) === 'object') {
    temp = subject
    subject = []
    for (i = 0; i < temp.length; i += 1) {
      subject[i] = temp[i].toLowerCase()
    }
  } else {
    subject = subject.toLowerCase()
  }
  if (typeof (search) === 'object' && typeof (replace) === 'string') {
    temp = replace
    replace = []
    for (i = 0; i < search.length; i += 1) {
      replace[i] = temp
    }
  }
  temp = ''
  f = [].concat(search)
  r = [].concat(replace)
  ra = Object.prototype.toString.call(r) === '[object Array]'
  s = subject
  s = [].concat(s)
  os = [].concat(os)
  if (countObj) {
    countObj.value = 0
  }
  for (i = 0, sl = s.length; i < sl; i++) {
    if (s[i] === '') {
      continue
    }
    for (j = 0, fl = f.length; j < fl; j++) {
      temp = s[i] + ''
      repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0]
      s[i] = (temp).split(f[j]).join(repl)
      otemp = os[i] + ''
      oi = temp.indexOf(f[j])
      ofjl = f[j].length
      if (oi >= 0) {
        os[i] = (otemp).split(otemp.substr(oi, ofjl)).join(repl)
      }
      if (countObj) {
        countObj.value += ((temp.split(f[j])).length - 1)
      }
    }
  }
  return osa ? os : os[0]
}
