function gmdate (format, timestamp) {
  //  discuss at: https://locutus.io/php/gmdate/
  // original by: Brett Zamir (https://brett-zamir.me)
  //    input by: Alex
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //   example 1: gmdate('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400); // Return will depend on your timezone
  //   returns 1: '07:09:40 m is month'



  const dt = typeof timestamp === 'undefined'
    ? new Date() // Not provided
    : timestamp instanceof Date
      ? new Date(timestamp) // Javascript Date()
      : new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)

  timestamp = Date.parse(dt.toUTCString().slice(0, -4)) / 1000

  return date(format, timestamp)
}
