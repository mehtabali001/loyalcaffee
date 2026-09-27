<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\ContactMessage;
use App\Models\Reservation;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormController extends Controller
{
    public function reserve(ReservationRequest $request): JsonResponse
    {
        $reservation = Reservation::create([
            ...$request->safe()->except('website'),
            'locale' => app()->getLocale(),
            'ip' => $request->ip(),
        ]);

        $this->notify(
            "New reservation {$reservation->reference}",
            "{$reservation->name} ({$reservation->phone}) — {$reservation->guests} guests on {$reservation->date->toDateString()} at {$reservation->time}".
            ($reservation->notes ? "\nNotes: {$reservation->notes}" : '')
        );

        return response()->json([
            'reference' => $reservation->reference,
            'status' => $reservation->status,
        ], 201);
    }

    public function contact(ContactRequest $request): JsonResponse
    {
        $msg = ContactMessage::create([
            ...$request->safe()->except('website'),
            'locale' => app()->getLocale(),
            'ip' => $request->ip(),
        ]);

        $this->notify("New message from {$msg->name}", $msg->message);

        return response()->json(['ok' => true], 201);
    }

    /** Best-effort staff email; a mail outage must never lose a booking. */
    private function notify(string $subject, string $body): void
    {
        $to = Setting::get('reservation.notify_email');
        if (! $to) {
            return;
        }
        try {
            Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('Staff notification failed: '.$e->getMessage());
        }
    }
}
