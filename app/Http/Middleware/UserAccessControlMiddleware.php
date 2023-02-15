<?php

namespace App\Http\Middleware;

use App\Access;
use Closure;

class UserAccessControlMiddleware
{
	private $strict_restrictions = [
		'orders/manual',
		'products_specifications',
		'backorders'
	];

	public function handle ($request, Closure $next)
	{
		// auth check is not necessary,
		// it's previously taken
		$uri = $request->path(); 
		// if the requested route is homepage,
		// then process the request
		// otherwise, check if the user has permission
		if ( url($uri) == url('/') || strpos($uri, "home") !== false || strpos($uri, "login") !== false || strpos($uri, "logout") !== false || $this->url_checker($uri) ) {
			return $next($request);
		}

		return app()->abort(403);
	}

	public function url_checker ($request_uri)
	{
		$should_strict = $this->mustMatchUrl($request_uri);
		$granted = auth()
			->user()
			->accesses()
			->get()
			->pluck('page'); 

		#dd($request_uri, $granted);
		#dd($request_uri);
		#dd(strpos($request_uri, "stations/bulk"));

		foreach ( $granted as $accessible ) {
			if ( strpos($request_uri, $accessible) !== false ) { // Yoshi version
				if ( $should_strict && $accessible != $request_uri ) {
					continue;
				}

				return true;
			}
		}

		return false;
	}

	private function mustMatchUrl ($request_uri)
	{
		foreach ( $this->strict_restrictions as $restriction ) {
			if ( $request_uri == $restriction ) {
				return true;
			}
		}

		return false;
	}
}
