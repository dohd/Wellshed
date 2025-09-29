<?php

namespace App\Http\Controllers\Focus\calendar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\calendar\StoreCalendarEventRequest;
use App\Http\Requests\Focus\calendar\UpdateCalendarEventRequest;
use App\Models\calendar\CalendarEvent;
use App\Models\hrm\Hrm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!access()->allow('view-calendar-events')) return redirect()->route('biller.dashboard');

        $employees = $employees = Hrm::get(['id', 'first_name', 'last_name']);

        $calendarEvents = CalendarEvent::all();
//            CalendarEvent::where('organizer', Auth::user()->id)
//            ->orwhereHas('eventParticipants', function($p) {
//                $p->where('user_id', Auth::user()->id);
//            })
//            ->get();

        return view('focus.calendar.index', compact('employees', 'calendarEvents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreCalendarEventRequest $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:1000'],
                'location' => ['required', 'string', 'max:255'],
                'organizer' => ['required', 'exists:users,id'],
                'participants' => ['required', 'array', 'min:1'],
                'participants.*' => ['exists:users,id'],
                'start' => ['required', 'date'],
                'end' => ['required', 'date', 'after:start'],
                'color' => ['required', 'string', 'size:7'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 400);
        }

        try {

            $start = new \DateTime($validated['start']);
            $end = new \DateTime($validated['end']);

            // Find overlapping participants only within the current event
            $overlappingParticipants = CalendarEvent::where(function ($query) use ($start, $end) {
                $query->whereBetween('start', [$start, $end])
                    ->orWhereBetween('end', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start', '<=', $start)
                            ->where('end', '>=', $end);
                    });
            })
                ->whereHas('eventParticipants', function ($q) use ($validated) {
                    $q->whereIn('user_id', $validated['participants']);
                })
                ->with(['eventParticipants' => function ($query) use ($validated) {
                    $query->whereIn('user_id', $validated['participants']);
                }])
                ->get()
                ->flatMap(function ($event) {
                    return $event->eventParticipants;
                })
                ->pluck('id')
                ->unique();

            if ($overlappingParticipants->isNotEmpty()) {
                // Fetch the full names of overlapping participants
                $overlappingNames = Hrm::whereIn('id', $overlappingParticipants)
                    ->get()
                    ->map(function ($user) {
                        return $user->first_name . ' ' . $user->last_name;
                    })
                    ->join(', ');

                return response()->json([
                    'success' => false,
                        'errors' => [
                            'participants' => "The following participants in this event have overlapping events: $overlappingNames."
                        ],
                    ],
                    400
                );
            }

            // Check if the organizer can book overlapping events
            $overlappingOrganizerEvents = CalendarEvent::where(function ($query) use ($start, $end) {
                $query->whereBetween('start', [$start, $end])
                    ->orWhereBetween('end', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start', '<=', $start)
                            ->where('end', '>=', $end);
                    });
            })
                ->where('organizer', $validated['organizer'])
                ->whereHas('eventParticipants', function ($q) use ($validated) {
                    $q->where('user_id', $validated['organizer']);
                })
                ->exists();

            if ($overlappingOrganizerEvents) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'participants' => 'The organizer is already a participant in an overlapping event.'
                    ],
                ],
                    400
                );
            }

            // Create the new CalendarEvent record
            $event = CalendarEvent::create([
                'event_number' => uniqid('CEV' . Auth::user()->ins . '-', true),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'organizer' => $validated['organizer'],
                'start' => $validated['start'],
                'end' => $validated['end'],
                'color' => $validated['color'],
            ]);

            // Attach participants to the event
            $event->eventParticipants()->attach($validated['participants']);

            // Commit the transaction
            DB::commit();

            $calendarEvents = CalendarEvent::all();

            // Return the updated event
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'events' => $calendarEvents,
            ], 201);

        } catch (Exception $ex) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Return error details
            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $eventNumber
     */
    public function update(UpdateCalendarEventRequest $request, $eventNumber)
    {
        DB::beginTransaction();

        try {
            // Validate the request
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:1000'],
                'location' => ['required', 'string', 'max:255'],
                'organizer' => ['required', 'exists:users,id'],
                'participants' => ['required', 'array', 'min:1'],
                'participants.*' => ['exists:users,id'],
                'start' => ['required', 'date'],
                'end' => ['required', 'date', 'after:start'],
                'color' => ['required', 'string', 'size:7'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 400);
        }

        try {
            $start = new \DateTime($validated['start']);
            $end = new \DateTime($validated['end']);

            // Find the event being updated
            $event = CalendarEvent::findOrFail($eventNumber);

            // Check for overlapping participants within the updated time range
            $overlappingParticipants = CalendarEvent::where('event_number', '!=', $eventNumber)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start', [$start, $end])
                        ->orWhereBetween('end', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start', '<=', $start)
                                ->where('end', '>=', $end);
                        });
                })
                ->whereHas('eventParticipants', function ($q) use ($validated) {
                    $q->whereIn('user_id', $validated['participants']);
                })
                ->with(['eventParticipants' => function ($query) use ($validated) {
                    $query->whereIn('user_id', $validated['participants']);
                }])
                ->get()
                ->flatMap(function ($event) {
                    return $event->eventParticipants;
                })
                ->pluck('id')
                ->unique();

            if ($overlappingParticipants->isNotEmpty()) {
                // Fetch the full names of overlapping participants
                $overlappingNames = Hrm::whereIn('id', $overlappingParticipants)
                    ->get()
                    ->map(function ($user) {
                        return $user->first_name . ' ' . $user->last_name;
                    })
                    ->join(', ');

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'participants' => "The following participants in this event have overlapping events: $overlappingNames.",
                    ],
                ], 400);
            }

            // Check if the organizer has overlapping events
            $overlappingOrganizerEvents = CalendarEvent::where('event_number', '!=', $eventNumber)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start', [$start, $end])
                        ->orWhereBetween('end', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start', '<=', $start)
                                ->where('end', '>=', $end);
                        });
                })
                ->where('organizer', $validated['organizer'])
                ->whereHas('eventParticipants', function ($q) use ($validated) {
                    $q->where('user_id', $validated['organizer']);
                })
                ->exists();

            if ($overlappingOrganizerEvents) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'participants' => 'The organizer is already a participant in an overlapping event.',
                    ],
                ], 400);
            }

            // Update the CalendarEvent record
            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'organizer' => $validated['organizer'],
                'start' => $validated['start'],
                'end' => $validated['end'],
                'color' => $validated['color'],
            ]);

            // Update participants
            $event->eventParticipants()->sync($validated['participants']);

            // Commit the transaction
            DB::commit();

            $calendarEvents = CalendarEvent::all();

            // Return the updated event
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'events' => $calendarEvents,
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Request $request, $event_number)
    {

        if (!access()->allow('delete-calendar-events')) return redirect()->route('biller.dashboard');


        DB::beginTransaction(); // Start transaction

        try {
            // Find the event by event_number
            $event = CalendarEvent::find($event_number);

            // Check if the event exists
            if (!$event) {
                return response()->json([
                    'message' => 'Event not found',
                ], 404);
            }

            // Check if the current user is the organizer of the event
            if ($event->organizer !== Auth::user()->id) {
                return response()->json([
                    'message' => 'You are not authorized to delete this event',
                ], 403);
            }

            // Detach participants and delete the event
            $event->eventParticipants()->detach();
            $event->delete();

            // Commit the transaction
            DB::commit();

            $calendarEvents = CalendarEvent::all();


            // Retrieve remaining events
//            $calendarEvents = CalendarEvent::where('organizer', Auth::user()->id)
//                ->orWhereHas('eventParticipants', function ($p) {
//                    $p->where('user_id', Auth::user()->id);
//                })
//                ->get();

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully',
                'events' => $calendarEvents,
            ], 200);

        } catch (Exception $ex) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Return error details
            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }
}
