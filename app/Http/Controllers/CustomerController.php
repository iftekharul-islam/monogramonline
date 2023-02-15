<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;

class CustomerController extends Controller
{

	public function index ()
	{
		$customers = Customer::latest()
							 ->paginate(50);
		$count = 1;

		return view('customers.index', compact('customers', 'count'));
	}

	public function create ()
	{
		return redirect()->back();

		return view('customers.create');
	}

	public function store (CustomerRequest $request)
	{
		$this->insertOrUpdateData($request);

		return redirect(url('/'));
	}

	public function show ($id)
	{
		$customer = Customer::find($id);
		if ( !$customer ) {
			return view('errors.404');
		}

		return view('customers.show', compact('customer'));
	}

	public function edit ($id)
	{
		return redirect()->back();
		$customer = Customer::find($id);
		if ( !$customer ) {
			return view('errors.404');
		}

		return view('customers.edit', compact('customer'));
	}

	public function update (Request $request, $id)
	{
		$customer = Customer::find($id);
		if ( !$customer ) {
			return view('errors.404');
		}

		$this->insertOrUpdateData($request, $id);

		return redirect(url('/'));
	}

	public function destroy ($id)
	{
		//
	}

	private function insertOrUpdateData ($request, $id = null)
	{
		$customer = null;
		if ( is_null($id) ) {
			$customer = new Customer();
		} else {
			$customer = Customer::find($id);
		}
		$customer->ship_full_name = $request->get('ship_full_name');
		$customer->company_name = $request->get('company_name');
		$customer->first_name = $request->get('first_name');
		$customer->last_name = $request->get('last_name');
		$customer->shipping_address_1 = $request->get('shipping_address_1');
		$customer->shipping_address_2 = $request->get('shipping_address_2');
		$customer->ship_city = $request->get('ship_city');
		$customer->ship_state = $request->get('ship_state');
		$customer->ship_country = $request->get('ship_country');
		$customer->ship_zip = $request->get('ship_zip');
		$customer->ship_phone = $request->get('ship_phone');
		$customer->bill_company_name = $request->get('bill_company_name');
		$customer->bill_first_name = $request->get('bill_first_name');
		$customer->bill_last_name = $request->get('bill_last_name');
		$customer->bill_address_1 = $request->get('bill_address_1');
		$customer->bill_address_2 = $request->get('bill_address_2');
		$customer->bill_city = $request->get('bill_city');
		$customer->bill_state = $request->get('bill_state');
		$customer->bill_country = $request->get('bill_country');
		$customer->bill_zip = $request->get('bill_zip');
		$customer->bill_phone = $request->get('bill_phone');
		$customer->save();
	}
}
