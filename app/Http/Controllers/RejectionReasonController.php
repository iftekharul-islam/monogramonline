<?php

namespace App\Http\Controllers;

use App\RejectionReason;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RejectionReasonController extends Controller
{
	public function index ()
	{

		$rejection_reasons = RejectionReason::where('is_deleted', 0)
											->orderBy('sort_order')
											->paginate(50);

		#return $stations_list;
		$count = 0;

		return view('rejection_reasons.index', compact('rejection_reasons', 'count'));
	}

	public function create ()
	{
		//
	}

	public function store (Requests\RejectionReasonCreateRequest $request)
	{
		$rejection_reason = new RejectionReason();
		$rejection_reason->rejection_message = trim($request->get('rejection_message'));
		$rejection_reason->sort_order = RejectionReason::max('sort_order') + 1;
		$rejection_reason->save();

		return redirect()->action('RejectionReasonController@index');
	}

	public function show ($id)
	{
		//
	}

	public function edit ($id)
	{
		//
	}

	public function update (Requests\RejectionReasonUpdateRequest $request, $id)
	{
		$rejection_reason = RejectionReason::find($id);
		if ( !$rejection_reason ) {
			return redirect()
				->back()
				->withInput()
				->withErrors([
					'invalid' => 'Cannot update. rejection message id invalid',
				]);
		}

		$rejection_reason->rejection_message = trim($request->get('updated_rejection_message'));
		$rejection_reason->save();

		return redirect()->action('RejectionReasonController@index');
	}
	
	public function sortOrder($direction, $id)
	{
		$reason = RejectionReason::find($id);
		
		if (!$reason) {
			Log::error('Rejection Reason sort: Reason not Found');
			return redirect()->action('RejectionReasonController@index')->withError('Reason not Found');
		}
	
		if ($direction == 'up') {
			$new_order = $reason->sort_order - 1;
		} else if ($direction == 'down') {
			$new_order = $reason->sort_order + 1;
		} else {
			Log::error('Rejection Reason sort: Direction not recognized');
			return redirect()->action('RejectionReasonController@index')->withError('Sort direction not recognized');
		}
		
		$switch = RejectionReason::where('sort_order', $new_order)->get();
		
		if (count($switch) > 1) {
			Log::error('Rejection Reason sort: More than one Rejection Reason with same sort order');
			return redirect()->action('RejectionReasonController@index')->withError('Sort Order Error');
		}
		
		if (count($switch) == 1) {
			$switch->first()->sort_order = $reason->sort_order;
			$switch->first()->save();
		}
		
		$reason->sort_order = $new_order;
		$reason->save();
		
		return redirect()->action('RejectionReasonController@index');
	}
	
	public function destroy ($id)
	{
		$rejection_reason = RejectionReason::find($id);
		
		if ( !$rejection_reason ) {
			return redirect()
				->back()
				->withInput()
				->withErrors([
					'invalid' => 'Cannot update. rejection message id invalid',
				]);
		}
		
		$move_up = RejectionReason::where('sort_order', '>', $rejection_reason->sort_order)->get();
		
		foreach ($move_up as $reason) {
			$reason->sort_order = $reason->sort_order - 1;
			$reason->save();
		}
		
		$rejection_reason->is_deleted = '1';
		$rejection_reason->sort_order = -1;
		$rejection_reason->save();
		
		return redirect()->action('RejectionReasonController@index');

	}
}
