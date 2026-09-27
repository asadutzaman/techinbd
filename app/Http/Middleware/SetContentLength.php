<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Tells the browser how long each response is, so it can use the page as soon as it has it.
 *
 * Customer emails are sent after the response (App\Support\CustomerMail). PHP-FPM hands the page over before
 * that work starts; Apache's mod_php and PHP's built-in server keep the connection open until the script ends,
 * so without a Content-Length the browser would wait for Gmail before showing the page or following a redirect.
 * (On Windows the built-in server also serves one request at a time, so the next page still waits there.)
 */
class SetContentLength
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('HEAD')
            || $response instanceof StreamedResponse
            || $response instanceof BinaryFileResponse
            || $response->headers->has('Content-Length')
            || $response->headers->has('Transfer-Encoding')) {
            return $response;
        }

        $content = $response->getContent();
        if ($content !== false) {
            $response->headers->set('Content-Length', (string) strlen($content));
        }

        return $response;
    }
}
