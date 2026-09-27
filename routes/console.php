<?php

use App\Models\Reservation;
use Illuminate\Support\Facades\Schedule;

// Housekeeping: mark stale pending bookings as no-show the morning after
Schedule::call(function () {
    Reservation::where('status', 'pending')->whereDate('date', '<', today()->subDay())->update(['status' => 'no_show']);
})->dailyAt('06:00')->name('reservations:expire');

// Drop expired admin tokens
Schedule::command('sanctum:prune-expired --hours=24')->daily();
