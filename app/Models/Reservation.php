<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'seated', 'cancelled', 'no_show'];

    protected $fillable = ['event_id', 'name', 'phone', 'email', 'guests', 'date', 'time', 'seating',
        'notes', 'locale', 'status', 'admin_notes', 'ip'];

    protected $casts = ['date' => 'date:Y-m-d', 'guests' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $r) {
            do {
                $ref = 'LY-'.Str::upper(Str::random(6));
            } while (static::where('reference', $ref)->exists());
            $r->reference = $ref;
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** wa.me deep link so staff can confirm with one tap, in the guest's language. */
    public function whatsappConfirmLink(): string
    {
        $phone = preg_replace('/\D+/', '', $this->phone);
        if (str_starts_with($phone, '05')) {
            $phone = '971'.substr($phone, 1);           // UAE local mobile → international
        } elseif (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        }

        $cafe = tr(Setting::get('site_name'), $this->locale) ?? 'Loyal Cafe';
        $when = $this->date->format('d/m').' '.$this->time;
        $msg = $this->locale === 'ar'
            ? "مرحباً {$this->name}، تم تأكيد حجزك في {$cafe} لـ {$this->guests} أشخاص يوم {$when}. رقم الحجز {$this->reference}. بانتظارك!"
            : "Hi {$this->name}, your table at {$cafe} for {$this->guests} on {$when} is confirmed. Ref {$this->reference}. See you soon!";

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($msg);
    }
}
