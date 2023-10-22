function strcmp (str1, str2) {
  //  discuss at: https://locutus.io/php/strcmp/
  // original by: Waldo Malqui Silva (https://waldo.malqui.info)
  //    input by: Steve Hilder
  // improved by: Kevin van Zonneveld (https://kvz.io)
  //  revised by: gorthaur
  //   example 1: strcmp( 'waldo', 'owald' )
  //   returns 1: 1
  //   example 2: strcmp( 'owald', 'waldo' )
  //   returns 2: -1

  return ((str1 === str2) ? 0 : ((str1 > str2) ? 1 : -1))
}
