function quotemeta (str) {
  return (str + '')
    .replace(/([.\\+*?[^\]$()])/g, '\\$1')
}
