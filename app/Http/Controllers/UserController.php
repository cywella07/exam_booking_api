<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class UserController extends Controller
{


    public function user_event()
    {
        $today = Carbon::today();

        $events = Event::whereDate('date', '>=', $today)
                    ->withCount('bookings') // this gives bookings_count
                    ->orderBy('date', 'asc')
                    ->get();

            return response()->json([
                'events' => $events->map(function($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'date' => $event->date,
                        'time' => $event->time,
                        'location' => $event->location,
                        'capacity' => (int) $event->capacity,
                        'booked' => (int) $event->bookings_count,
                        'description' => $event->description,
                    ];
            })
        ]);
    }


    public function user_reserved(Request $request)
    {
        $user = Auth::user();
        $eventId = $request->input('event_id');

        $event = Event::findOrFail($eventId);

        // Check if already booked
        $alreadyBooked = Booking::where('user_id', $user->id)
                                ->where('event_id', $eventId)
                                ->exists();

        if ($alreadyBooked) {
            return response()->json(['message' => 'You have already reserved this event.'], 400);
        }

        // Check if full
        $bookedCount = $event->bookings()->count();
        if ($bookedCount >= $event->capacity) {
            return response()->json(['message' => 'This event is already fully booked.'], 400);
        }

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
        Mail::to($user->email)->send(new BookingConfirmation($event));
        return response()->json(['message' => 'Event reserved successfully.', 'booking' => $booking]);
    }

}
