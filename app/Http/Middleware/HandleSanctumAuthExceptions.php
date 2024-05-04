<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HandleSanctumAuthExceptions
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['error' => 'Token expirado o inválido'], 401);
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Error de autenticación'], 401);
        }
    }
}
