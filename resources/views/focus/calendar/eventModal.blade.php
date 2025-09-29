<!-- Modal Container -->
<div id="eventModal"
     style="display: none; max-height: 700px; max-width: 600px; margin: auto; background-color: #fff;
           padding: 20px; border: 1px solid #ccc; border-radius: 10px;
           position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
           z-index: 1050; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">


    <h2 class="text-center" id="modalTitle">Add Event</h2>

    @php

        $canViewEvent = access()->allow('view-calendar-events');
        $canCreateEvent = access()->allow('create-calendar-events');
        $canEditEvent = access()->allow('edit-calendar-events');
        $canDeleteEvent = access()->allow('delete-calendar-events');

    @endphp


            <!-- Scrollable Content Wrapper -->
    <div style="overflow-y: auto; overflow-x: hidden; padding-right: 10px; max-height: 600px; box-sizing: border-box;">

        <!-- Event Form Content -->
        <div class="mb-1">
            <input type="text" id="eventNumber" required class="form-control" hidden>

            <label for="eventTitle">Event Title <span style="color: red">*</span></label>
            <input type="text" id="eventTitle" required class="form-control" placeholder="Give your event a Title">
            <label id="titleError" class="text-danger" style="display: none"></label>
        </div>

        <div class="mb-1">
            <label for="eventDescription">Agenda <span style="color: red">*</span></label>
            <textarea id="eventDescription" class="form-control" rows="3" placeholder="Provide a brief description of your event"></textarea>
            <label id="descriptionError" class="text-danger" style="display: none"></label>
        </div>

        <div class="mb-1">
            <label for="eventLocation">Location <span style="color: red">*</span></label>
            <input type="text" id="eventLocation" required class="form-control" placeholder="Eg. Board Room 4">
            <label id="locationError" class="text-danger" style="display: none"></label>
        </div>

        <div class="mb-1">
            <label for="eventOrganizer">Event Organizer <span style="color: red">*</span></label>
            <select id="eventOrganizer" name="eventOrganizer" class="form-control select-box select2" required data-placeholder="Select an Organizer">
                <option value="">Select an Organizer</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name . ' ' . $emp->last_name }}</option>
                @endforeach
            </select>
            <label id="organizerError" class="text-danger" style="display: none"></label>
        </div>

        <div class="mb-1">
            <label for="eventParticipants" class="form-label">Participants</label>
            <select id="eventParticipants" name="eventParticipants[]" class="form-control select-box select2" multiple required placeholder="Add Participants to Your Event">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name . ' ' . $emp->last_name }}</option>
                @endforeach
            </select>
            <label id="participantsError" class="text-danger" style="display: none"></label>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-lg-6">
                <label for="eventStart">Start Time <span style="color: red">*</span></label>
                <input type="datetime-local" id="eventStart" required class="form-control">
                <label id="startError" class="text-danger" style="display: none"></label>
            </div>
            <div class="col-12 col-lg-6">
                <label for="eventEnd">End Time <span style="color: red">*</span></label>
                <input type="datetime-local" id="eventEnd" required class="form-control">
                <label id="endError" class="text-danger" style="display: none"></label>
            </div>
        </div>

        <div class="mb-3">
            <label for="eventColor">Event Color <span style="color: red">*</span></label>
            <input type="color" id="eventColor" value="#718FFA" class="form-control" style="height: 40px;">
            <label id="colorError" class="text-danger" style="display: none"></label>
        </div>

        @if($canDeleteEvent)
            <button id="deleteEvent" class="btn btn-danger mr-2 mt-1" style="width: 30%; display: none;">
                <i class="fa fa-trash"></i> Delete Event
            </button>
        @endif


        <!-- Buttons -->
        <div class="d-flex justify-content-end mt-3">
            <button id="cancelEvent" class="btn btn-yellow mr-2" style="width: 30%;">
                <span style="color: black">Cancel</span>
            </button>
            <button id="saveEvent" class="btn btn-primary" style="width: 30%;">Save Event</button>
        </div>

    </div>




</div>


<style>

    #eventModal {
        overflow: hidden; /* Prevent content from overflowing modal */
    }

    #eventModal div[style*="overflow-y: auto"] {
        max-height: calc(100% - 100px); /* Ensure it doesn't extend beyond modal bounds */
        padding-right: 10px;
    }

    body.modal-open {
        overflow: hidden; /* Prevent background scroll */
    }

</style>