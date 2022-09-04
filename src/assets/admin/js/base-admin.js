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
});
