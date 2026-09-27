<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/** Picks the content locale from: route {locale} → X-Locale header → ?lang → Accept-Language. */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $candidates = [
            $request->route('locale'),
            $request->header('X-Locale'),
            $request->query('lang'),
            $request->getPreferredLanguage(Locale::all()),
        ];

        foreach ($candidates as $c) {
            if (Locale::isSupported($c)) {
                App::setLocale($c);
                break;
            }
        }

        $response = $next($request);
        $response->headers->set('Content-Language', App::getLocale());
        $response->headers->set('Vary', trim(($response->headers->get('Vary') ?? '').', X-Locale', ', '));

        return $response;
    }
}
