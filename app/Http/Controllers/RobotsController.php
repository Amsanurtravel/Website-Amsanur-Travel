<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Only production is indexable, so a staging copy never competes with the
     * real site in Google.
     */
    public function __invoke(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /wa', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
