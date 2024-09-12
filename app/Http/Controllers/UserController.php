<?php namespace App\Http\Controllers;

use App\Access;
use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;

class UserController extends Controller
{

    protected $vendors = [
        'VENDOR-A' => 'Vendor-A',
        'VENDOR-B' => 'Vendor-B',
        'VENDOR-C' => 'Vendor-C',
        'VENDOR-D' => 'Vendor-D',
        'VENDOR-E' => 'Vendor-E'
    ];
	public function index ()
	{
		$count = 1;
		$users = User::where('is_deleted', 0)
					 ->latest()
					 ->paginate(50);

		return view('users.index', compact('users', 'count'));
	}
	
	public function barcode ($id) 
	{
		$user = User::find($id);
					
		$user_code = 'USER' . intval($id * 8) . '9';

		$name = $user->username;
		
		return view('prints.print_user_id', compact('user_code', 'name'));
	}
	
	public function create ()
	{
        $vendors = $this->vendors;
		
		return view('users.create', compact('vendors'));
	}
	
	public function store (UserRequest $request)
	{
		$user = new User();
		$user->username = trim($request->get('username'));
		$user->email = $request->get('email');
		$user->password = $request->get('password');
		$user->vendor = $request->get('vendor');
		$user->vendor_id = $request->get('vendor_id');
		$user->zip_code = $request->get('zip_code');
		$user->state = $request->get('state');
		if ($request->has('remote')) {
			$user->remote = $request->get('remote');
		} else {
			$user->remote = '0';
		}
		$user->save();

		$requested_accesses = $request->get('user_access');
		$matched = array_intersect($requested_accesses, array_keys(Access::$pages)); // get the matched pages
		foreach ( $matched as $match ) {
			$access = new Access();
			$access->user_id = $user->id;
			$access->page = $match;
			$access->save();
		}

		return redirect(url('users'));

	}

	public function show ($id)
	{
		$user = User::where('is_deleted', 0)
					->find($id);
		if ( !$user ) {
			return view('errors.404');
		}
		
		return view('users.show', compact('user'));
	}

	public function edit ($id)
	{
		#return auth()->user()->accesses->pluck('page')->toArray();
		$user = User::where('is_deleted', 0)
					->find($id);
		if ( !$user ) {
			return view('errors.404');
		}

        $vendors = $this->vendors;
		
		return view('users.edit', compact('user', 'vendors'));
	}

	public function update (UserUpdateRequest $request, $id)
	{
		$user = User::where('is_deleted', 0)
					->find($id);
		if ( !$user ) {
			return view('errors.404');
		}
		$user->username = trim($request->get('username'));
		if ( $request->has('email') ) {
			$user->email = $request->get('email');
		}
		if ( $request->has('password') ) {
			$user->password = $request->get('password');
		}
		$user->vendor_id = $request->get('vendor_id');
		$user->zip_code = $request->get('zip_code');
		$user->state = $request->get('state');

        if ($request->has('vendor')) {
            $user->vendor = $request->get('vendor');
        } else {
            $user->vendor = null;
        }

		if ($request->has('remote')) {
			$user->remote = $request->get('remote');
		} else {
			$user->remote = '0';
		}
		
		$user->save();

		// delete previous accesses
		$user->accesses()
			 ->delete();
		// get the new accesses
		$requested_accesses = $request->get('user_access');
		$matched = array_intersect($requested_accesses, array_keys(Access::$pages)); // get the matched pages
		foreach ( $matched as $match ) {
			$access = new Access();
			$access->user_id = $user->id;
			$access->page = $match;
			$access->save();
		}

		return redirect(url('users'));
	}

	public function destroy ($id)
	{
		$user = User::where('is_deleted', 0)
					->find($id);
		if ( !$user ) {
			return view('errors.404');
		}

		$user->is_deleted = 1;
		$user->save();

		return redirect(url('users'));
	}
}
