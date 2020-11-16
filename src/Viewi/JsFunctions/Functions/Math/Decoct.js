function decoct (number) {
  if (number < 0) {
    number = 0xFFFFFFFF + number + 1
  }
  return parseInt(number, 10)
    .toString(8)
}
