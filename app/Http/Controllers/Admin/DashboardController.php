<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\MenuItem;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $active = ['pending', 'confirmed', 'seated'];

        $week = collect(range(0, 6))->map(function ($i) use ($active) {
            $d = today()->addDays($i);

            return [
                'date' => $d->toDateString(),
                'reservations' => Reservation::whereDate('date', $d)->whereIn('status', $active)->count(),
                'guests' => (int) Reservation::whereDate('date', $d)->whereIn('status', $active)->sum('guests'),
            ];
        });

        return response()->json([
            'today' => [
                'reservations' => Reservation::whereDate('date', today())->whereIn('status', $active)->count(),
                'guests' => (int) Reservation::whereDate('date', today())->whereIn('status', $active)->sum('guests'),
                'pending' => Reservation::where('status', 'pending')->whereDate('date', '>=', today())->count(),
            ],
            'unread_messages' => ContactMessage::whereNull('read_at')->count(),
            'menu_items' => MenuItem::count(),
            'next_7_days' => $week,
            'next_matches' => Event::active()->upcoming()->limit(5)
                ->withSum(['reservations as seats_taken' => fn ($r) => $r->whereIn('status', $active)], 'guests')
                ->get(['id', 'title', 'home_team', 'away_team', 'starts_at', 'capacity', 'is_big_match']),
            'latest_reservations' => Reservation::with('event:id,title')->latest()->limit(6)->get(),
        ]);
    }
}
