<?php

namespace App\Http\Middleware;

use App\Services\UserAiProviderConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserAPITokens
{
    public function __construct(private readonly UserAiProviderConfigService $userAiProviderConfigService) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->userAiProviderConfigService->applyForUser($request->user()?->getAuthIdentifier());

        return $next($request);
    }
}
