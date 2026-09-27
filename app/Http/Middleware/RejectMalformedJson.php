<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responds with 400 Bad Request when a JSON body cannot be parsed into an object/array.
 * A syntactically valid body with invalid values is handled by validation (422).
 */
class RejectMalformedJson
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = $request->getContent();

        if ($request->isJson() && trim($content) !== '') {
            try {
                $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                return $this->badRequest('Malformed JSON body: '.$exception->getMessage().'.');
            }

            if (! is_array($decoded)) {
                return $this->badRequest('The JSON body must be an object.');
            }
        }

        return $next($request);
    }

    protected function badRequest(string $message): Response
    {
        return response()->json(['message' => $message], Response::HTTP_BAD_REQUEST);
    }
}
