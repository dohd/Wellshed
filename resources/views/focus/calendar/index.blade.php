@extends ('core.layouts.app')

@section ('title', 'Events Calendar')

@section('content')

    <head>
        <!-- Latest CSS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js"></script>
        <script src="https://www.jsdelivr.com/package/npm/@fullcalendar/timegrid?version=6.1.15"></script>
        <script src="https://www.jsdelivr.com/package/npm/@fullcalendar/list?version=6.1.15"></script>
        <script src="https://www.jsdelivr.com/package/npm/@fullcalendar/multimonth?version=6.1.15"></script>
        <script src="https://www.jsdelivr.com/package/npm/@fullcalendar/scrollgrid?version=6.1.15"></script>

        <!-- Tippy.js CSS -->
        <link href="https://unpkg.com/tippy.js@6/dist/tippy.css" rel="stylesheet">

        <!-- Tippy.js JavaScript -->
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
    </head>



    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Events Calendar </h2>
            </div>

        </div>


        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">

                                <!-- Create a div for the calendar -->
                                <div id="calendar"></div>

                                <!-- Modal for event creation -->
                                @include('focus.calendar.eventModal')

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $('.select2').select2({
            placeholder: "Select Participants",  // Placeholder for Select2
            allowClear: true,
            width: '100%'  // Ensures it takes up the full width of the container
        });

        let startDate = null;

        let newEvents = [];

        var isNewEvent = true;

        let calendarEvents = @json($calendarEvents);

        document.addEventListener('DOMContentLoaded', function() {

            let canViewEvent = @json(access()->allow('view-calendar-events'));
            let canCreateEvent = @json(access()->allow('create-calendar-events'));
            let canEditEvent = @json(access()->allow('edit-calendar-events'));
            let canDeleteEvent = @json(access()->allow('delete-calendar-events'));


            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'addEventButton',    // Left section with navigation buttons
                    center: 'title',            // Center section with the calendar title
                    right: 'today prev,next dayGridMonth,timeGridWeek,timeGridDay'  // Right section with view options
                },
                initialView: 'dayGridMonth',
                editable: false, // important for activating event interactions!
                selectable: true, // important for activating date selectability!
                events: calendarEvents,
                eventContent: function (info) {
                    const viewType = info.view.type;

                    // Create a wrapper div for the event
                    const wrapper = document.createElement('div');
                    wrapper.style.display = 'inline-block'; // Inline-block for width based on content
                    wrapper.style.whiteSpace = 'nowrap'; // Prevent wrapping
                    wrapper.style.padding = '4px 8px'; // Add padding for a clean look
                    wrapper.style.border = '1px solid #ddd'; // Light border for structure
                    wrapper.style.borderRadius = '4px'; // Rounded corners
                    wrapper.style.backgroundColor = info.event.extendedProps.organizer === @json(\Illuminate\Support\Facades\Auth::user()->id) ? '#Ffd700' : '#fff'; // White for month view
                    wrapper.style.marginRight = '10px'; // Margin to the right

                    // Colored circle
                    const colorCircle = document.createElement('span');
                    colorCircle.style.display = 'inline-block';
                    colorCircle.style.width = '16px';
                    colorCircle.style.height = '16px';
                    colorCircle.style.borderRadius = '50%';
                    colorCircle.style.backgroundColor = info.event.backgroundColor || info.event.color;
                    colorCircle.style.marginRight = '8px';

                    // Event title
                    const eventTitle = document.createElement('span');
                    eventTitle.className = 'fc-event-title';
                    eventTitle.textContent = info.event.title;
                    eventTitle.style.fontSize = '16px';
                    eventTitle.style.fontWeight = 'bold'; // Bold for emphasis
                    eventTitle.style.color = '#333'; // Darker text for better contrast
                    eventTitle.style.marginRight = '10px'; // Darker text for better contrast
                    eventTitle.style.paddingTop = '3px'; // Darker text for better contrast


                    const categoryWrapper = document.createElement('div');
                    categoryWrapper.style.display = 'inline-block'; // Inline-block for width based on content
                    categoryWrapper.style.whiteSpace = 'nowrap'; // Prevent wrapping
                    categoryWrapper.style.padding = '4px 8px'; // Add padding for a clean look
                    categoryWrapper.style.border = '1px solid #ddd'; // Light border for structure
                    categoryWrapper.style.borderRadius = '20px'; // Rounded corners
                    categoryWrapper.style.backgroundColor = info.event.extendedProps.organizer === @json(\Illuminate\Support\Facades\Auth::user()->id) ? '#4c2b82' : '#865097'; // White for month view
                    categoryWrapper.style.marginRight = '10px'; // Margin to the right


                    const eventCategory = document.createElement('span');
                    eventCategory.className = 'fc-event-category';
                    eventCategory.textContent = info.event.extendedProps.category;
                    eventCategory.style.fontSize = '16px';
                    eventCategory.style.fontWeight = 'bold'; // Bold for emphasis
                    eventCategory.style.color = '#ffffff'; // Darker text for better contrast
                    eventCategory.style.padding = '10px'; // Darker text for better contrast

                    categoryWrapper.appendChild(eventCategory);

                    // Append colored circle and title to the wrapper
                    wrapper.appendChild(colorCircle);
                    wrapper.appendChild(eventTitle);
                    wrapper.appendChild(categoryWrapper);



                    // Organizer name (common for all views)
                    const organizerName = document.createElement('div');
                    organizerName.className = 'fc-event-organizer';
                    organizerName.textContent = `🧑🏾 Organizer: ${info.event.extendedProps.organizer_name}`;
                    organizerName.style.fontSize = '14px';
                    organizerName.style.color = '#ffffff';
                    organizerName.style.marginTop = '4px';

                    if (viewType === 'dayGridMonth') {
                        // Display location for month view on a new line
                        const eventLocation = document.createElement('div');
                        eventLocation.className = 'fc-event-location';
                        eventLocation.textContent = `📍 ${info.event.extendedProps.location}`;
                        eventLocation.style.fontSize = '14px'; // Smaller text for details
                        eventLocation.style.color = '#ffffff'; // Muted color for less emphasis
                        eventLocation.style.marginTop = '4px'; // Spacing for clarity

                        return { domNodes: [wrapper, eventLocation, organizerName] };
                    }

                    if (viewType === 'timeGridDay') {
                        // Additional details for day view
                        const eventLocation = document.createElement('div');
                        eventLocation.className = 'fc-event-location';
                        eventLocation.textContent = `📍 Location: ${info.event.extendedProps.location}`;
                        eventLocation.style.fontWeight = 'bold';
                        eventLocation.style.fontSize = '14px';
                        eventLocation.style.color = '#ffffff';
                        eventLocation.style.marginTop = '4px';

                        return { domNodes: [wrapper, eventLocation, organizerName] };
                    }

                    // Default: Only the title with colored circle and organizer name
                    return { domNodes: [wrapper, organizerName] };
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps) {
                        const participantNames = info.event.extendedProps.participant_names.split(', ');

                        // Check if there are more than 8 participants
                        const participantContent =
                            participantNames.length > 8
                            ? `
                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 1;">
                                        ${participantNames.slice(0, Math.ceil(participantNames.length / 2)).map(name => `- ${name}`).join('<br>')}
                                    </div>
                                    <div style="flex: 1;">
                                        ${participantNames.slice(Math.ceil(participantNames.length / 2)).map(name => `- ${name}`).join('<br>')}
                                    </div>
                                </div>
                            `
                            : participantNames.map(name => `- ${name}`).join('<br>');

                        // Build the tooltip content
                        const tooltipContent = `
                            <div style="text-align: left;">
                                <strong>Event Name:</strong> ${info.event.title}<br><br>
                                <strong>Category:</strong> ${info.event.extendedProps.category}<br><br>
                                <strong>Organizer:</strong> ${info.event.extendedProps.organizer_name}<br><br>
                                <strong>Location:</strong> ${info.event.extendedProps.location}<br><br>
                                <strong>Starts on:</strong> ${info.event.start}<br>
                                <strong>Ends on:</strong> ${info.event.end}<br><br>
                                <strong><u>Agenda</u></strong> <br> ${info.event.extendedProps.description}<br><br>
                                <strong> <u>Participants</u> </strong ><br>
                                ${participantContent}
                            </div>
                        `;

                        // Initialize the tooltip with tippy.js
                        tippy(info.el, {
                            content: tooltipContent,
                            allowHTML: true,
                            placement: 'top',
                            theme: 'light', // Optional: add a theme for styling
                            interactive: true, // Optional: allows mouse interactions with the tooltip
                        });
                    }
                },
                eventClick: function (info) {

                    if (canEditEvent) {

                        if (info.event.extendedProps.organizer === {{ \Illuminate\Support\Facades\Auth::user()->id }}) editEvent(info.event);
                    }
                },
                customButtons: canCreateEvent ?
                    {
                    addEventButton: {
                        text: 'Add Event',  // Label for the button
                        click: function() {

                            isNewEvent = true;

                            // Open the modal when the button is clicked
                            document.getElementById('eventModal').style.display = 'block';
                            document.body.classList.add('modal-open');

                            document.getElementById('saveEvent').textContent = 'Save Event';

                            // Reset modal inputs
                            document.getElementById('eventTitle').value = null;
                            document.getElementById('eventNumber').value = null;
                            document.getElementById('eventDescription').value = null;
                            document.getElementById('eventLocation').value = null;
                            $(document.querySelector('#eventOrganizer')).val(null).trigger('change');
                            $(document.querySelector('#eventParticipants')).val([]).trigger('change');
                            document.getElementById('eventStart').value = null;
                            document.getElementById('eventEnd').value = null;
                            document.getElementById('eventColor').value = '#718FFA';

                            deleteButton.style.display = 'none';
                        }
                    }
                }
                : false,
                dateClick: function(info) {
                    // Switch to Day View when a date is clicked
                    calendar.changeView('timeGridDay', info.dateStr);
                },
            });

            // // Adding an event programmatically
            // calendar.addEvent({
            //     title: 'Sample Event',
            //     start: '2024-11-25T10:00:00',
            //     end: '2024-11-25T12:00:00',
            //     description: 'This is a sample event.',
            //     backgroundColor: '#ff9f89',  // Custom background color
            //     textColor: '#000',  // Custom text color
            // });

            calendar.render();


            // Handle Save Event
            document.getElementById('saveEvent').onclick = function() {

                const eventTitle = document.getElementById('eventTitle').value;
                const eventNumber = document.getElementById('eventNumber').value;
                const eventDescription = document.getElementById('eventDescription').value;
                const eventLocation = document.getElementById('eventLocation').value;
                const eventOrganizer = document.getElementById('eventOrganizer').value;
                const eventParticipants = $(document.querySelector('#eventParticipants')).val();
                const eventStart = document.getElementById('eventStart').value;
                const eventEnd = document.getElementById('eventEnd').value;
                const eventColor = document.getElementById('eventColor').value;
                const modal = document.getElementById('eventModal');

                if (eventTitle && eventStart && eventEnd) {

                    const eventData = {
                        event_number: eventNumber,
                        title: eventTitle,
                        description: eventDescription,
                        location: eventLocation,
                        organizer: eventOrganizer,
                        participants: eventParticipants,
                        start: eventStart,
                        end: eventEnd,
                        color: eventColor,
                    };

                    // Add the new event to the calendar

                    newEvents.push(eventData);


                    if (!isNewEvent) {

                        if (confirm('Are you sure you want to update this event?')) {

                            $.ajax({
                                url: 'calendar/' +eventData.event_number, // Update with your server endpoint
                                type: 'PUT',
                                data: eventData,
                                success: function (response) {

                                    console.clear();
                                    console.table(response);
                                    console.log(response);


                                    calendar.getEventSources().forEach(event => {
                                        event.remove();
                                    });

                                    response.events.forEach(event => {
                                        calendar.addEvent(event);
                                    });


                                    modal.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                },
                                error: function (xhr, status, error) {
                                    // Parse the response JSON if available
                                    const errorData = xhr.responseJSON;

                                    // Select all potential error labels on the form
                                    const errorFields = document.querySelectorAll('[id$="Error"]');

                                    if (errorData && errorData.errors) {
                                        // Hide all error labels initially
                                        errorFields.forEach(errorField => {
                                            errorField.style.display = 'none';
                                            errorField.textContent = ''; // Clear previous error messages
                                        });

                                        // Iterate over the errors and display them in the respective error fields
                                        Object.keys(errorData.errors).forEach(field => {
                                            const errorField = document.getElementById(`${field}Error`);
                                            if (errorField) {
                                                // Show the error message for the specific field
                                                errorField.textContent = errorData.errors[field];
                                                errorField.style.display = 'block';
                                            }
                                        });
                                    } else {
                                        // Hide all error labels if no structured errors are returned
                                        errorFields.forEach(errorField => {
                                            errorField.style.display = 'none';
                                            errorField.textContent = ''; // Clear any lingering messages
                                        });

                                        // Log a generic error message if no detailed error data is available
                                        console.error('Error fetching data:', error);
                                    }

                                    // Log additional error details if available
                                    if (errorData) {
                                        console.error('Error details:', errorData);

                                        // Display a detailed table for debugging purposes
                                        console.table({
                                            message: errorData.message || 'No message provided',
                                            code: errorData.code || 'N/A',
                                            file: errorData.file || 'N/A',
                                            line: errorData.line || 'N/A',
                                        });
                                    } else {
                                        console.error('Unexpected error:', error);
                                    }
                                }
                            });

                        } else {
                            // If user cancels, you can handle that here
                            console.log('Action cancelled');
                        }
                    }

                    else {

                        $.ajax({
                            url: '{{ route("biller.calendar.store") }}', // Update with your server endpoint
                            type: 'POST',
                            data: eventData,
                            success: function (response) {

                                console.clear();
                                console.table(response);
                                console.log(response);


                                calendar.getEventSources().forEach(event => {
                                    event.remove();
                                });

                                response.events.forEach(event => {
                                    calendar.addEvent(event);
                                });


                                modal.style.display = 'none';
                                document.body.classList.remove('modal-open');
                            },
                            error: function (xhr, status, error) {
                                // Parse the response JSON if available
                                const errorData = xhr.responseJSON;

                                // Select all potential error labels on the form
                                const errorFields = document.querySelectorAll('[id$="Error"]');

                                if (errorData && errorData.errors) {
                                    // Hide all error labels initially
                                    errorFields.forEach(errorField => {
                                        errorField.style.display = 'none';
                                        errorField.textContent = ''; // Clear previous error messages
                                    });

                                    // Iterate over the errors and display them in the respective error fields
                                    Object.keys(errorData.errors).forEach(field => {
                                        const errorField = document.getElementById(`${field}Error`);
                                        if (errorField) {
                                            // Show the error message for the specific field
                                            errorField.textContent = errorData.errors[field];
                                            errorField.style.display = 'block';
                                        }
                                    });
                                } else {
                                    // Hide all error labels if no structured errors are returned
                                    errorFields.forEach(errorField => {
                                        errorField.style.display = 'none';
                                        errorField.textContent = ''; // Clear any lingering messages
                                    });

                                    // Log a generic error message if no detailed error data is available
                                    console.error('Error fetching data:', error);
                                }

                                // Log additional error details if available
                                if (errorData) {
                                    console.error('Error details:', errorData);

                                    // Display a detailed table for debugging purposes
                                    console.table({
                                        message: errorData.message || 'No message provided',
                                        code: errorData.code || 'N/A',
                                        file: errorData.file || 'N/A',
                                        line: errorData.line || 'N/A',
                                    });
                                } else {
                                    console.error('Unexpected error:', error);
                                }
                            }
                        });
                    }


                    console.clear();
                    console.table(newEvents);
                } else {
                    alert('Please fill out all fields');
                }
            };

            // Handle Cancel Event
            document.getElementById('cancelEvent').onclick = function() {

                document.getElementById('eventModal').style.display = 'none';
                document.body.classList.remove('modal-open');
            };


            function editEvent(event) {

                isNewEvent = false;

                console.clear();
                console.log(event);

                // Get modal elements
                const modal = document.getElementById('eventModal');
                const modalTitle = document.getElementById('modalTitle');
                const eventNumber = document.getElementById('eventNumber');
                const eventTitle = document.getElementById('eventTitle');
                const eventDescription = document.getElementById('eventDescription');
                const eventLocation = document.getElementById('eventLocation');
                const eventOrganizer = $('#eventOrganizer'); // Use jQuery for select2 fields
                const eventParticipants = $('#eventParticipants');
                const eventStart = document.getElementById('eventStart');
                const eventEnd = document.getElementById('eventEnd');
                const eventColor = document.getElementById('eventColor');
                const deleteButton = document.getElementById('deleteEvent');


                document.getElementById('saveEvent').textContent = 'Update Event';

                // Check if the event is being edited (existing event) or adding a new one
                if (event) {

                    console.table(event.extendedProps.event_number);

                    modalTitle.textContent = 'Edit Event'; // Set title for editing
                    eventNumber.value = event.extendedProps.event_number || '';
                    eventTitle.value = event.title || '';
                    eventDescription.value = event.extendedProps.description || '';
                    eventLocation.value = event.extendedProps.location || '';
                    eventOrganizer.val(event.extendedProps.organizer || '').trigger('change'); // Set and refresh select2
                    eventParticipants.val(event.extendedProps.participants || []).trigger('change'); // Set and refresh select2
                    eventStart.value = event.start ? event.start.toISOString().slice(0, 16) : ''; // Format to datetime-local
                    eventEnd.value = event.end ? event.end.toISOString().slice(0, 16) : ''; // Format to datetime-local
                    eventColor.value = event.backgroundColor || '#718FFA';

                    deleteButton.style.display = 'block';
                }
                else {

                    modalTitle.textContent = 'Add Event'; // Set title for adding a new event
                    // Clear all fields for a new event
                    eventTitle.value = '';
                    eventDescription.value = '';
                    eventLocation.value = '';
                    eventOrganizer.val('').trigger('change'); // Clear and refresh select2
                    eventParticipants.val([]).trigger('change'); // Clear and refresh select2
                    eventStart.value = '';
                    eventEnd.value = '';
                    eventColor.value = '#718FFA';

                    deleteButton.style.display = 'none';

                }

                // Display the modal
                modal.style.display = 'block';

                // Prevent body from scrolling
                document.body.classList.add('modal-open');

                // Add event listener for cancel button
                document.getElementById('cancelEvent').onclick = function () {
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                };
            }



            document.getElementById('deleteEvent').onclick = function() {

                if (confirm('Are you sure you want to delete this event?')) {

                    const eventNumber = document.getElementById('eventNumber').value;
                    const modal = document.getElementById('eventModal');

                    $.ajax({
                        url: 'calendar/' + eventNumber, // Update with your server endpoint
                        type: 'DELETE',
                        data: {},
                        success: function (response) {

                            console.clear();
                            console.table(response);
                            console.log(response);


                            calendar.getEventSources().forEach(event => {
                                event.remove();
                            });

                            response.events.forEach(event => {
                                calendar.addEvent(event);
                            });

                            modal.style.display = 'none';
                            document.body.classList.remove('modal-open');
                        },
                        error: function (xhr, status, error) {
                            // Parse the response JSON if available
                            const errorData = xhr.responseJSON;

                            if (errorData && errorData.errors) {
                                // Iterate over the errors and display them in the respective error fields
                                Object.keys(errorData.errors).forEach(field => {
                                    const errorField = document.getElementById(`${field}Error`);
                                    if (errorField) {
                                        // Show the error message for the specific field
                                        errorField.textContent = errorData.errors[field];
                                        errorField.style.display = 'block';
                                    }
                                });
                            } else {
                                // Log a generic error message if no detailed error data is available
                                console.error('Error fetching data:', error);
                            }

                            // Log additional error details if available
                            if (errorData) {
                                console.error('Error details:', errorData);

                                // Display a detailed table for debugging purposes
                                console.table({
                                    message: errorData.message || 'No message provided',
                                    code: errorData.code || 'N/A',
                                    file: errorData.file || 'N/A',
                                    line: errorData.line || 'N/A',
                                });
                            } else {
                                console.error('Unexpected error:', error);
                            }
                        }
                    });


                }
                else {

                }

            };

        });




        // document.addEventListener('click', function (event) {
        //     const modal = document.getElementById('eventModal');
        //     const addEventButton = document.querySelector('.fc-addEventButton-button'); // FullCalendar custom button
        //
        //     // Check if the modal is open, the clicked target is not inside the modal, and the target is not the custom button
        //     if (
        //         modal.style.display === 'block' &&
        //         !modal.contains(event.target) &&
        //         event.target !== addEventButton
        //     ) {
        //         modal.style.display = 'none'; // Close the modal
        //         document.body.classList.remove('modal-open'); // Allow scrolling on the page
        //     }
        // });
        //
        // // Prevent closing the modal when clicking inside it
        // document.getElementById('eventModal').addEventListener('click', function (e) {
        //     e.stopPropagation();
        // });


    </script>


    <style>

        /* Simple CSS for the modal */
        #eventModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            z-index: 1000;
            width: 500px;
        }

    </style>
@endsection