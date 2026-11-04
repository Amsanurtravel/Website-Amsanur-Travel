<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPages
{
    /**
     * Mark successful GET responses as publicly cacheable (CDN, LiteSpeed) and
     * answer conditional requests with 304. Errors are never marked cacheable,
     * so a 404 seen before an article goes live is not kept by a cache.
     */
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        $response = $next($request);

        if ($request->isMethodCacheable() && $response->isSuccessful()) {
            $response->setPublic();
            $response->setMaxAge($minutes * 60);
            $response->setEtag(md5((string) $response->getContent()));
            $response->isNotModified($request);
        }

        return $response;
    }
}
