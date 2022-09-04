/* JS
 * ----------------------------------
 */
// RegExp to require all (.js-) assets in '../admin/js'-directory.
let jsContext = require.context('../admin/js', true, /\.js$/);
if (jsContext.length >= 0) {
  jsContext.keys().forEach(function (key) {
    'use strict';

    jsContext(key);
  });
}
