<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Seo;
use App\Services\SiteContent;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class SpaController extends Controller
{
    public function __construct(private readonly SiteContent $content, private readonly Seo $seo) {}

    /** "/" → best locale for the visitor. */
    public function root(Request $request)
    {
        $locale = $request->cookie('locale');
        if (! Locale::isSupported($locale)) {
            $locale = $request->getPreferredLanguage(Locale::all()) ?? Locale::default();
        }

        return redirect("/$locale", 302);
    }

    public function site(Request $request, string $locale, ?string $page = null): Response
    {
        App::setLocale($locale);
        $page = $page ?: 'home';
        $known = in_array($page, config('loyal.pages'), true);

        $bootstrap = $this->content->bootstrap();
        $prefetch = match ($page) {
            'home' => ['home' => $this->content->home()],
            'menu' => ['menu' => ['categories' => $this->content->menu()]],
            'gallery' => ['gallery' => ['items' => $this->content->gallery()]],
            'match-nights', 'reserve' => ['events' => ['events' => $this->content->events()]],
            default => [],
        };

        return response()
            ->view('site', [
                'locale' => $locale,
                'dir' => Locale::isRtl($locale) ? 'rtl' : 'ltr',
                'meta' => $this->seo->forPage($known ? $page : 'home', $locale),
                'bootstrap' => $bootstrap + ['prefetch' => $prefetch, 'meta' => $this->seo->titles($locale)],
                'theme' => Setting::get('theme', []),
                'analytics' => Setting::get('analytics', []),
                'page' => $page,
                'content' => $prefetch,
            ], $known ? 200 : 404)
            ->withCookie(cookie()->forever('locale', $locale, httpOnly: false));
    }

    public function admin(): Response
    {
        return response()->view('admin')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
