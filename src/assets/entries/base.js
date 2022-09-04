/* JS
 * ----------------------------------
 */
// RegExp to require all (.js-) assets in '../js'-directory.
let jsContext = require.context('../js', true, /\.js$/);
if (jsContext.length >= 0) {
  jsContext.keys().forEach(function (key) {
    'use strict';

    jsContext(key);
  });
}
