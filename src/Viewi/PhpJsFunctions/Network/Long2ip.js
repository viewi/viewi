function long2ip (ip) {
  //  discuss at: https://locutus.io/php/long2ip/
  // original by: Waldo Malqui Silva (https://fayr.us/waldo/)
  //   example 1: long2ip( 3221234342 )
  //   returns 1: '192.0.34.166'

  if (!isFinite(ip)) {
    return false
  }

  return [ip >>> 24 & 0xFF, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.')
}
