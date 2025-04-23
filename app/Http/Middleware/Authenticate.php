<?php

namespace App\Http\Middleware;


use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $result = parent::handle($request, $next, $guards);

        $auth_user = Auth::user();

        $user = User::findOrFail($auth_user->id);

        if (! (bool) $user->is_active) {
            Session::flash("fail", 'User is de-activated');
            Auth::logout();
            return redirect("/login");
        }

        return $result;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
