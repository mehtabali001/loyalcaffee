<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cfg = Setting::get('reservation', []);

        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^[+0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'guests' => ['required', 'integer', 'min:'.($cfg['min_guests'] ?? 1), 'max:'.($cfg['max_guests'] ?? 30)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before:+90 days'],
            'time' => ['required', 'date_format:H:i'],
            'seating' => ['nullable', 'in:indoor,outdoor,screens'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $cfg = Setting::get('reservation', []);
            if (! ($cfg['enabled'] ?? true)) {
                $v->errors()->add('date', __('Online reservations are paused. Please call or message us on WhatsApp.'));

                return;
            }

            $tz = config('app.timezone');
            $at = CarbonImmutable::createFromFormat('Y-m-d H:i', $this->input('date').' '.$this->input('time'), $tz);
            if ($at->lt(now($tz)->addMinutes((int) ($cfg['lead_minutes'] ?? 30)))) {
                $v->errors()->add('time', __('Please choose a time at least :m minutes from now.', ['m' => $cfg['lead_minutes'] ?? 30]));
            }

            if (! $this->withinOpeningHours($at)) {
                $v->errors()->add('time', __('We are closed at that time. Please pick another slot.'));
            }

            if ($this->input('event_id') && ($event = Event::find($this->input('event_id')))) {
                if (! $event->reservations_open || ! $event->is_active) {
                    $v->errors()->add('event_id', __('Reservations for this match are closed.'));
                } elseif ($event->capacity !== null && $event->seatsTaken() + (int) $this->input('guests') > $event->capacity) {
                    $v->errors()->add('guests', __('Not enough seats left for this match. Try a smaller group or message us.'));
                }
            }
        }];
    }

    /**
     * Hours are stored per weekday (0 = Sunday) as HH:MM; a close time earlier than open means
     * past midnight (e.g. 08:00 → 03:00). A 01:30 booking therefore belongs to the previous day's shift.
     */
    private function withinOpeningHours(CarbonImmutable $at): bool
    {
        $hours = collect(Setting::get('hours', []))->keyBy('day');
        if ($hours->isEmpty()) {
            return true;
        }

        foreach ([$at, $at->subDay()] as $shiftDay) {
            $h = $hours->get($shiftDay->dayOfWeek);
            if (! $h || ($h['closed'] ?? false)) {
                continue;
            }
            $open = $shiftDay->setTimeFromTimeString($h['open']);
            $close = $shiftDay->setTimeFromTimeString($h['close']);
            if ($close->lte($open)) {
                $close = $close->addDay();
            }
            if ($at->gte($open) && $at->lt($close)) {
                return true;
            }
        }

        return false;
    }
}
