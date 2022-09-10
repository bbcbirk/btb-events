/*
* base-admin.js
* Place your custom admin specific js here...
*/
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar_view');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        eventSources: [
            {
                url: btb_events_js.event_feed,
                extraParams: {
                    'validation': btb_events_js.nonce
                }
            }
        ],
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },
        weekNumbers: true,
        weekNumberCalculation: "ISO"
    });
    calendar.render();

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
});
