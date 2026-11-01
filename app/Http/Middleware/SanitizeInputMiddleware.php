<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Fields excluded from HTML stripping.
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
    ];

    /**
     * Strip potential XSS attack payloads from request inputs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if (! empty($input)) {
            array_walk_recursive($input, function (&$value, $key) {
                if (is_string($value) && ! in_array($key, $this->except, true)) {
                    // Strip HTML and script tags to prevent stored/reflected XSS
                    $value = strip_tags($value);
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }
}
