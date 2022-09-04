/**
 * Wrap applicable code in vanilla DOM ready function, usually most functions can go inside this wrapper.
 */
document.addEventListener("DOMContentLoaded", function (event) {
  (function ($) {
    "use strict";

    testFunction();

    /**
     * testFunction description.
     */
    function testFunction() {
      // your code goes here
    }
  })(jQuery);
  /**
   * End Wrap code in vanilla DOM ready function.
   */
});
