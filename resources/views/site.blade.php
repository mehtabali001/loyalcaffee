@php
    $s = $bootstrap['settings'];
    $t = fn ($v) => is_array($v) ? tr($v, $locale) : $v;
    // Admin-editable brand colours → RGB triplets consumed by Tailwind (rgb(var(--x-rgb) / a))
    $themeVars = collect(['primary' => '--c-emerald', 'accent' => '--c-gold', 'background' => '--c-ivory', 'text' => '--c-charcoal', 'warm' => '--c-terracotta'])
        ->filter(fn ($var, $key) => ! empty($theme[$key]) && preg_match('/^#[0-9a-f]{6}$/i', $theme[$key]))
        ->map(fn ($var, $key) => $var.'-rgb:'.implode(' ', sscanf($theme[$key], '#%02x%02x%02x')))->implode(';');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <link rel="canonical" href="{{ $meta['canonical'] }}">
    @foreach ($meta['alternates'] as $hl => $href)
        <link rel="alternate" hreflang="{{ $hl }}" href="{{ $href }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $meta['x_default'] }}">

    <meta property="og:type" content="restaurant.restaurant">
    <meta property="og:site_name" content="{{ $t($s['site_name'] ?? 'Loyal Restaurant & Cafe') }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    <meta property="og:locale" content="{{ $meta['og_locale'] }}">
    @if ($meta['og_image'])
        <meta property="og:image" content="{{ $meta['og_image'] }}">
        <meta name="twitter:image" content="{{ $meta['og_image'] }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="{{ $theme['primary'] ?? '#1B4332' }}">
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" href="{{ $s['favicon'] ?? '/favicon.svg' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if ($dir === 'rtl')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@400;500;600&family=Cairo:wght@500;600&display=swap" rel="stylesheet">
    @endif

    @if ($themeVars)
        <style>:root{ {{ $themeVars }} }</style>
    @endif

    @foreach ($meta['json_ld'] as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endforeach

    <script>window.__LOYAL__ = {!! json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!};</script>

    @if (! empty($analytics['ga4_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics['ga4_id'] }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($analytics['ga4_id']));</script>
    @endif
    @if (! empty($analytics['meta_pixel_id']))
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',@json($analytics['meta_pixel_id']));fbq('track','PageView');</script>
    @endif

    @vite(['resources/css/site.css', 'resources/js/site/main.js'])
</head>
<body>
    <div id="app">
        {{-- Crawlable fallback — replaced by the Vue app on mount --}}
        <main class="seo-fallback">
            <h1>{{ $meta['title'] }}</h1>
            <p>{{ $meta['description'] }}</p>
            <address>
                {{ $t($s['address']['street'] ?? null) }}, {{ $t($s['address']['area'] ?? null) }}<br>
                <a href="tel:{{ $s['phone_intl'] ?? '' }}">{{ $s['phone'] ?? '' }}</a>
            </address>
            @if ($page === 'menu' && ! empty($content['menu']['categories']))
                @foreach ($content['menu']['categories'] as $cat)
                    <section>
                        <h2>{{ $t($cat['name'] ?? '') }}</h2>
                        <ul>
                            @foreach ($cat['items'] as $item)
                                <li>{{ $t($item['name'] ?? '') }} @if (! empty($item['price'])) — AED {{ rtrim(rtrim(number_format((float) $item['price'], 2), '0'), '.') }} @endif</li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            @endif
            <nav>
                @foreach (config('loyal.pages') as $p)
                    <a href="/{{ $locale }}{{ $p === 'home' ? '' : '/'.$p }}">{{ $p }}</a>
                @endforeach
            </nav>
        </main>
    </div>
</body>
</html>
