<?php

namespace App\Http\Middleware;

use App\Utils\CacheKey;
use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Client
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->input('token');
        if (empty($token)) {
            abort(403, 'token is null');
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            abort(403, 'token is error');
        }
        if (config('app.subscribeurl_auth') == "true") {
            if (!str_contains($request->getHost(), $user->uuid)) {
                abort(403, $request->getHost().' subscribeurl is error');
            }
        }
        $request->merge([
            'user' => $user
        ]);
        return $next($request);
    }
}
