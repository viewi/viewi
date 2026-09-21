function decoct(number) {
  //  discuss at: https://www.php.net/manual/en/function.decoct.php
  // The 64-bit unsigned value, as PHP: decoct(-1) is all ones.
  return BigInt.asUintN(64, BigInt(_php_cast_int(number))).toString(8)
}
