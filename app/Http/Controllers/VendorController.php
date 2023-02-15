<?php

namespace App\Http\Controllers;

use App\Vendor;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class VendorController extends Controller
{
	public function index (Request $request)
	{
		
		$vendors = Vendor::where('is_deleted', 0)
						 ->orderBy('vendor_name')
						 ->search($request->get('search_in'), $request->get('search_for'))
						 ->latest()
						 ->paginate(50);
						 
		$search_in = [
		 			'vendor_name'       => 'Vendor Name',
		 			'id'                => 'Vendor ID',
		 		];
				
		return view('vendors.index', compact('vendors', 'request', 'search_in'));
	}

	public function create ()
	{
		return view('vendors.create');
	}

	public function store (Requests\VendorCreateRequest $request)
	{
		$vendor = new Vendor();
		$vendor->vendor_name = trim($request->get('vendor_name'));
		$vendor->email = trim($request->get('email'));
		$vendor->phone_number = trim($request->get('phone_number'));

		$vendor->contact_person_name = trim($request->get('contact_person_name'));
		$vendor->link = trim($request->get('account_link'));
		$vendor->login_id = trim($request->get('account_login'));
		$vendor->password = trim($request->get('account_password'));

		$vendor->bank_info = trim($request->get('bank_info'));
		$vendor->paypal_info = trim($request->get('paypal_info'));
		$vendor->notes = trim($request->get('notes'));

		if ( $request->hasFile('image') ) {
			$vendor->image = $this->image_manipulator($request->file('image'), $request->get('vendor_name'));
		}
		$vendor->zip_code = trim($request->get('zip_code'));
		$vendor->state = trim($request->get('state'));
		$vendor->country = trim($request->get('country'));

		/*if ( $request->has('zip_code') ) {
			$vendor->zip_code = trim($request->get('zip_code'));
		}
		if ( $request->has('state') ) {
			$vendor->state = trim($request->get('state'));
		}
		if ( $request->has('country') ) {
			$vendor->country = trim($request->get('country'));
		}*/

		$vendor->save();

		session()->flash('success', 'Vendor created successfully.');

		return redirect()->action('VendorController@index', ['search_in' => 'id', 'search_for' => $vendor->id]);
	}

	public function show ($id)
	{
		$vendor = Vendor::find($id);
		if ( !$vendor ) {
			return view('errors.404');
		}

		return view('vendors.show', compact('vendor'));
	}

	public function edit ($id)
	{
		$vendor = Vendor::find($id);
		if ( !$vendor ) {
			return view('errors.404');
		}

		return view('vendors.edit', compact('vendor'));
	}

	public function update (Requests\VendorUpdateRequest $request, $id)
	{
		$vendor = Vendor::find($id);
		if ( !$vendor ) {
			return view('errors.404');
		}

		$vendor->vendor_name = trim($request->get('vendor_name'));
		$vendor->phone_number = trim($request->get('phone_number'));

		$vendor->contact_person_name = trim($request->get('contact_person_name'));
		$vendor->link = trim($request->get('account_link'));
		$vendor->login_id = trim($request->get('account_login'));

		$vendor->bank_info = trim($request->get('bank_info'));
		$vendor->paypal_info = trim($request->get('paypal_info'));
		$vendor->notes = trim($request->get('notes'));

		if ( $request->hasFile('image') ) {
			$vendor->image = $this->image_manipulator($request->file('image'), $request->get('vendor_name'));
		}


		$vendor->zip_code = trim($request->get('zip_code'));
		$vendor->state = trim($request->get('state'));
		$vendor->country = trim($request->get('country'));

		if ( $request->has('email') ) {
			$vendor->email = trim($request->get('email'));
		}
		if ( $request->has('account_password') ) {
			$vendor->password = $request->get('account_password');
		}

		$vendor->save();

		session()->flash('success', 'Vendor is successfully updated.');

		return redirect()->action('VendorController@index', ['search_in' => 'id', 'search_for' => $vendor->id]);
	}

	public function destroy ($id)
	{
		$vendor = Vendor::find($id);
		if ( !$vendor ) {
			return view('errors.404');
		}

		$vendor->is_deleted = 1;
		$vendor->save();

		session()->flash('success', 'Vendor successfully deleted.');

		return redirect()->action('VendorController@index');

	}

	private function image_manipulator ($image, $name)
	{
		if ( $image->isValid() ) {
			$destinationPath = 'assets/images/vendor_images';
			$extension = $image->getClientOriginalExtension();
			$fileName = sprintf("%s-%s.%s", str_slug($name), rand(11111, 99999), $extension);
			$image->move($destinationPath, $fileName);

			return sprintf("%s/%s", url($destinationPath), $fileName);
		}

		return '';
	}
}
