/**
 * Wrap applicable code in vanilla DOM ready function, usually most functions can go inside this wrapper.
 */
document.addEventListener("DOMContentLoaded", function (event) {
  (function ($) {
    "use strict";

    initAddToCalendar();

    /**
     * Initialize Add To Calendar buttons
     */
    function initAddToCalendar() {
      $('.btb_event_add_to_calendar').on('click', function () {
        let event_id = $(this).data('event-id');
        let settings = JSON.parse(Buffer.from($(this).data('event-settings'), 'base64'));
        console.log(settings);
        let button = document.querySelector('.btb_event_add_to_calendar[data-event-id="' + event_id + '"]');
        atcb_action(settings, button);
      });
    }
  })(jQuery);
  /**
   * End Wrap code in vanilla DOM ready function.
   */
});
