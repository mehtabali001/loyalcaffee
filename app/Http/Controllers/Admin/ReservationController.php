<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    private function query(Request $request)
    {
        $q = Reservation::query()->with('event:id,title,starts_at');

        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('reference', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        }
        if ($request->filled('event_id')) {
            $q->where('event_id', $request->query('event_id'));
        }
        match ($request->query('range')) {
            'today' => $q->whereDate('date', today()),
            'upcoming' => $q->whereDate('date', '>=', today()),
            'past' => $q->whereDate('date', '<', today()),
            default => null,
        };
        if ($request->filled('date')) {
            $q->whereDate('date', $request->query('date'));
        }

        return $request->query('range') === 'past'
            ? $q->orderByDesc('date')->orderByDesc('time')
            : $q->orderBy('date')->orderBy('time');
    }

    public function index(Request $request): JsonResponse
    {
        $page = $this->query($request)->paginate($request->integer('per_page', 25));
        $page->getCollection()->transform(fn (Reservation $r) => [...$r->toArray(), 'whatsapp_link' => $r->whatsappConfirmLink()]);

        return response()->json($page);
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(Reservation::STATUSES)],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'guests' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'date' => ['sometimes', 'date'],
            'time' => ['sometimes', 'date_format:H:i'],
        ]);
        $reservation->update($data);

        return response()->json([...$reservation->fresh('event')->toArray(), 'whatsapp_link' => $reservation->whatsappConfirmLink()]);
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->delete();

        return response()->json(['ok' => true]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($request)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic names correctly
            fputcsv($out, ['Reference', 'Name', 'Phone', 'Email', 'Guests', 'Date', 'Time', 'Seating', 'Match', 'Status', 'Notes']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->reference, $r->name, $r->phone, $r->email, $r->guests, $r->date->toDateString(),
                    $r->time, $r->seating, $r->event ? tr($r->event->title, 'en') : '', $r->status, $r->notes]);
            }
            fclose($out);
        }, 'reservations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
