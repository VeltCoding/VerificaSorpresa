<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware implements Middleware
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            return $this->respondWithCors($handler->handle($request), $request);
        }
        
        return $this->respondWithCors($handler->handle($request), $request);
    }

    private function respondWithCors(Response $response, Request $request): Response
    {
        $origin = $request->getHeaderLine('Origin');
        if (!$origin) {
            $origin = '*';
        }
        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }
}
