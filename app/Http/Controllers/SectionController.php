<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Section;
use App\Station;

class SectionController extends Controller
{
		public function index(Request $request)
		{
			$section_list = Section::where ( 'is_deleted', 0 )->lists ( 'section_name', 'id' )->prepend ( 'No Section assigned', '' );
			
			$sections = Section::where ( 'is_deleted', 0 )->get();
			
			$anchor = $request->get('station');
			
			return view ( 'sections.index', compact ( 'sections', 'section_list', 'anchor' ) );
		}
		
		
		public function assign(Request $request)
		{
			$station = Station::find($request->get('station'));
			$station->section = $request->get('section');
			$station->save();
			
			return redirect(url('/prod_config/sections#' . $station->station_name));
		}
		

		public function store(Request $request)
		{
			if ($request->has('section')) {
				$section = Section::find($request->get('section'));
			} else {
				$section = new Section();
			}
			$section->section_name = $request->get('section_name');
			$section->summaries = $request->get('summaries') ?? '0';
			$section->start_finish = $request->get('start_finish') ?? '0';
			$section->same_user = $request->get('same_user') ?? '0';
			$section->print_label = $request->get('print_label') ?? '0';
			$section->inventory = $request->get('inventory') ?? '0';
			$section->inv_control = $request->get('inv_control') ?? '0';
			$section->save();
			
			return redirect(url('/prod_config/sections'));
		}
		
		
		public function delete(Request $request) {
			$this->destroy($request->get('section'));
			return redirect(url('/prod_config/sections'));
		}
		
		
		public function destroy($id)
		{
			$section = Section::find($id);
			if ( ! $section ) {
				return view('errors.404');
			}
			$section->is_deleted = 1;
			$section->save();
			
			$stations = Station::where ('section', $id)->get();
			
			foreach ($stations as $station) {
				$station = Station::find($station->id);
				$station->section = 0;
				$station->save();
			}
			
		}
		
}
