function base_convert (number, frombase, tobase) { 
  return parseInt(number + '', frombase | 0)
    .toString(tobase | 0)
}
