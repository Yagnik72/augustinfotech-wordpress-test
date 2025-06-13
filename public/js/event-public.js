jQuery(document).ready(function($) {
    var calendarEl = document.getElementById('event-calendar');
    var modal = document.getElementById('event-modal');
    var closeBtn = document.getElementsByClassName('event-modal-close')[0];

    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: event_ajax.ajax_url,
                    type: 'GET',
                    data: {
                        action: 'get_events',
                        nonce: event_ajax.nonce,
                        status: 'approved'
                    },
                    success: function(response) {
                        successCallback(response);
                    },
                    error: function(error) {
                        failureCallback(error);
                    }
                });
            },
            eventClick: function(info) {
                var event = info.event;
                $('#event-modal-title').text(event.title);
                $('#event-modal-description').html(event.extendedProps.description);
                
                var details = '<p><strong>Date:</strong> ' + event.start.toLocaleDateString() + '</p>';
                details += '<p><strong>Time:</strong> ' + event.start.toLocaleTimeString() + '</p>';
                if (event.extendedProps.event_manager) {
                    details += '<p><strong>Event Manager:</strong> ' + event.extendedProps.event_manager + '</p>';
                }
                
                $('#event-modal-details').html(details);
                modal.style.display = 'block';
            }
        });

        calendar.render();
    }

    // Close modal when clicking the close button
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
}); 