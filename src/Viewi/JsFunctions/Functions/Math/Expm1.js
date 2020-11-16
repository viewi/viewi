function expm1 (x) {
  return (x < 1e-5 && x > -1e-5)
    ? x + 0.5 * x * x
    : Math.exp(x) - 1
}
