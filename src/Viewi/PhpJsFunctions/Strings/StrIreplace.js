function str_ireplace (search, replace, subject, countObj) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/str_ireplace/
  // original by: Glen Arason (https://CanadianDomainRegistry.ca)
  // bugfixed by: Mahmoud Saeed
  //      note 1: Case-insensitive version of str_replace()
  //      note 1: Compliant with PHP 5.0 str_ireplace() Full details at:
  //      note 1: https://ca3.php.net/manual/en/function.str-ireplace.php
  //      note 2: The countObj parameter (optional) if used must be passed in as a
  //      note 2: object. The count will then be written by reference into it's `value` property
  //   example 1: str_ireplace('M', 'e', 'name')
  //   returns 1: 'naee'
  //   example 2: var $countObj = {}
  //   example 2: str_ireplace('M', 'e', 'name', $countObj)
  //   example 2: var $result = $countObj.value
  //   returns 2: 1
  //   example 3: str_ireplace('', '.', 'aaa')
  //   returns 3: 'aaa'

  let i = 0
  let j = 0
  let temp = ''
  let repl = ''
  let sl = 0
  let fl = 0
  let f = ''
  let r = ''
  let s = ''
  let ra = ''
  let otemp = ''
  let oi = ''
  let ofjl = ''
  let os = subject
  const osa = Object.prototype.toString.call(os) === '[object Array]'
  // var sa = ''

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
  // sa = Object.prototype.toString.call(s) === '[object Array]'
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
      if (f[j] === '') {
        continue
      }
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
