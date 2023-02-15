<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard; 

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }
        
        if ($this->auth->user()->remote == '0' &&
              (substr($request->ip(), 0 ,8) != '10.10.0.' && $request->ip() != '96.57.0.130' && $request->ip() != '127.0.0.1')) {
          
          return response('Unauthorized.', 401);
        }
        
        return $next($request);
    }
}
