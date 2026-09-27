<?php

namespace App\Http\Controllers;

use App\Support\Locale;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach (config('loyal.pages') as $page) {
            $path = $page === 'home' ? '' : "/$page";
            foreach (Locale::all() as $l) {
                $xml .= "  <url>\n    <loc>".e(url("/$l$path"))."</loc>\n";
                foreach (Locale::all() as $alt) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'.$alt.'" href="'.e(url("/$alt$path")).'"/>'."\n";
                }
                $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.e(url('/'.Locale::default().$path)).'"/>'."\n";
                $xml .= '    <changefreq>'.($page === 'match-nights' ? 'daily' : 'weekly')."</changefreq>\n";
                $xml .= '    <priority>'.($page === 'home' ? '1.0' : '0.8')."</priority>\n  </url>\n";
            }
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /api/\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
