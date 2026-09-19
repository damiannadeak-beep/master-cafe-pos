<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * Generate robots.txt response for SEO and crawlers.
     */
    public function robots()
    {
        $content = "User-agent: *\n" .
                   "Allow: /\n" .
                   "Allow: /katalog\n" .
                   "Allow: /lokasi\n" .
                   "Allow: /kontak\n" .
                   "Allow: /images/\n" .
                   "Disallow: /admin/\n" .
                   "Disallow: /kasir/\n" .
                   "Disallow: /waitress/\n" .
                   "Disallow: /dapur/\n" .
                   "Disallow: /login\n\n" .
                   "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Generate sitemap.xml response for Google Search indexing.
     */
    public function sitemap()
    {
        $baseUrl = url('/');
        $lastMod = date('Y-m-d');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" .
               '  <url><loc>' . $baseUrl . '/</loc><lastmod>' . $lastMod . '</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n" .
               '  <url><loc>' . $baseUrl . '/katalog</loc><lastmod>' . $lastMod . '</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>' . "\n" .
               '  <url><loc>' . $baseUrl . '/lokasi</loc><lastmod>' . $lastMod . '</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>' . "\n" .
               '  <url><loc>' . $baseUrl . '/kontak</loc><lastmod>' . $lastMod . '</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>' . "\n" .
               '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
