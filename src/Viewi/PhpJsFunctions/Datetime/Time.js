function time () {
  //  discuss at: https://locutus.io/php/time/
  // original by: GeekFG (https://geekfg.blogspot.com)
  // improved by: Kevin van Zonneveld (https://kvz.io)
  // improved by: metjay
  // improved by: HKM
  //   example 1: var $timeStamp = time()
  //   example 1: var $result = $timeStamp > 1000000000 && $timeStamp < 2000000000
  //   returns 1: true

  return Math.floor(new Date().getTime() / 1000)
}
