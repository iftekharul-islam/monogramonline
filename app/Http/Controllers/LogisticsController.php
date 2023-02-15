<?php namespace App\Http\Controllers;

use App\BatchRoute;
use App\Design;
use App\Http\Requests\OptionUpdateRequest;
use App\Inventory;
use App\InventoryUnit;
use App\Option;
use App\Parameter;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Monogram\Crawler;
use Monogram\Helper;

class LogisticsController extends Controller
{

    public function parameters(Request $request)
    {

        $parameters = Parameter::where('is_deleted', 0)
            ->get();
        $index = 1;

        return view('logistics.parameters', compact('parameters', 'index'));
    }

    public function update_parameters(Request $request)
    {
        $parameters = array_unique($request->get('parameters'));

        if (count($request->get('parameters')) !== count($parameters)) {
            $duplicates = array_unique(array_diff_assoc($request->get('parameters'), array_unique($request->get('parameters'))));
            $duplicates = implode(",", $duplicates);

// 			dd($request->get('parameters'), $parameters,$duplicates);

            return redirect()
                ->back()
                ->withErrors([
                    'error' => "Can not insert duplicate kayes: " . $duplicates,
                ]);
        }

        #Parameter::where('store_id', $store_id)->delete();
        if ($request->has('parameters')) {
            /*foreach ( $request->get('parameters') as $parameter_value ) {
                $parameter = new Parameter();
                $parameter->store_id = $store_id;
                $parameter->parameter_value = trim($parameter_value);
                $parameter->save();
            }*/
            $this->insert_parameters_into_table($request->get('parameters'));
            session()->flash('success', 'successfully Updated.');
        }

        return redirect()->action('LogisticsController@parameters');
    }

    private function insert_parameters_into_table($parameters)
    {
        Parameter::where('is_deleted', '0')
            ->delete();
        // filter the empty values array_filter($parameters)
        // create new rows with parameter_value and store_id
        $rows = array_map(function ($row) {
            return [
                'parameter_value' => trim($row),
            ];
        }, array_filter($parameters));

        Parameter::insert($rows);
    }

    public function sku_list(Request $request)
    {
        if ($request->get('unassigned')) {
            $unassigned = $request->get('unassigned');
        } else {
            $unassigned = 0;
        }

        if ($request->get('skus') == null) {
            $options = Option::with('product', 'route.template', 'inventoryunit_relation.inventory', 'design')
                ->leftjoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
                ->searchIn($request->get('search_for_first'), $request->get('contains_first'), $request->get('search_in_first'), $request->get('stockno'))
                ->searchIn($request->get('search_for_second'), $request->get('contains_second'), $request->get('search_in_second'), $request->get('stockno'))
                ->searchIn($request->get('search_for_third'), $request->get('contains_third'), $request->get('search_in_third'), $request->get('stockno'))
                ->searchIn($request->get('search_for_fourth'), $request->get('contains_fourth'), $request->get('search_in_fourth'), $request->get('stockno'))
                ->searchRoute($request->get('batch_route_id'))
                ->searchActive($request->get('active'))
                ->searchStatus($request->get('sku_status'))
                ->searchSure3d($request->get('sure3d'))
                ->selectRaw('parameter_options.*, inventory_unit.stock_no_unique')
                ->groupBy('parameter_options.child_sku')
                ->orderBy('parameter_options.parent_sku', 'ASC')
                ->paginate(100);
        } else {
            $options = Option::with('product', 'route.template', 'inventoryunit_relation.inventory', 'design')
                ->leftjoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
                ->whereIn('parameter_options.child_sku', $request->get('skus'))
                ->groupBy('parameter_options.child_sku')
                ->orderBy('parameter_options.parent_sku', 'ASC')
                ->paginate(100);
        }

        $batch_routes = BatchRoute::where('is_deleted', 0)
            ->orderBy('batch_route_name')
            ->get()
            ->pluck('batch_route_name', 'id')
            ->prepend('', 0);
        // ->get();


        $stock_no_list = Inventory::select(DB::raw('CONCAT(stock_no_unique, " - ", stock_name_discription) AS description'),
            'stock_no_unique', 'warehouse')
            ->where('is_deleted', 0)
            ->orderBy('stock_no_unique')
            ->get();

        $searchable = [
            '' => 'Search In',
            'parent_sku' => 'Parent SKU',
            'child_sku' => 'Child SKU',
            'id_catalog' => 'ID Catalog',
            'stock_number' => 'Stock Number',
            'graphic_sku' => 'Graphic SKU',
            'name' => 'Name'
        ];

        $request->has('batch_route_id') ? $batch_route_id = $request->get('batch_route_id') : $batch_route_id = '';

        $operators = ['in' => 'In',
            'not_in' => 'Not In',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'equals' => 'Equals',
            'not_equals' => 'Not Equal',
            // 'less_than' => 'Less Than',
            // 'greater_than' => 'Greater Than',
            'blank' => 'Is Blank',
            'not_blank' => 'Is Not Blank'
        ];

        #return $parameters;
        return view('logistics.sku_list', compact('stock_no_list', 'batch_routes', 'batch_route_id',
            // return view('logistics.sku_list', compact('stock_no_list', 'batch_routes', 'batch_route_id',
            'options', 'request', 'search_for_first', 'search_in_first', 'search_for_second', 'search_in_second',
            'childSkus', 'unassigned', 'searchable', 'operators'));

    }

    public function update_skus(OptionUpdateRequest $request)
    {
        if ($request->has('child_skus')) {
            $skus = array_filter($request->get('child_skus'));

            $skus = array_map('htmlspecialchars_decode', $skus);

            if (count($skus) > 0) {

                $update = array();

                if ($request->has('allow_mixing_update') && $request->get('allow_mixing_update') != '') {
                    $update['allow_mixing'] = $request->get('allow_mixing_update');
                }

                if ($request->has('batch_route_id_update') && $request->get('batch_route_id_update') != 0) {
                    $update['batch_route_id'] = $request->get('batch_route_id_update');
                }

                if ($request->has('graphic_sku_update') && $request->get('graphic_sku_update') != '') {
                    $update['graphic_sku'] = $request->get('graphic_sku_update');
                }

                if ($request->has('sure3d_update') && $request->get('sure3d_update') != '') {
                    $update['sure3d'] = $request->get('sure3d_update');
                }

                if ($request->has('frame_size_update') && $request->get('frame_size_update') != '') {
                    $update['frame_size'] = $request->get('frame_size_update');
                }
//dd($update, $request->all());
                if (count($update) > 0) {

                    if (auth()->user()) {
                        $update['user_id'] = auth()->user()->id;
                    } else {
                        $update['user_id'] = 87;
                    }

                    $records = Option::whereIn('child_sku', $skus)
                        ->update($update);
                }

                if ($request->has('stocknos')) {

                    foreach ($skus as $sku) {

                        InventoryUnit::where('child_sku', $sku)->delete();

                        foreach ($request->get('stocknos') as $stock_no) {
                            $unit = new InventoryUnit;
                            $unit->child_sku = $sku;
                            $unit->stock_no_unique = $stock_no;
                            $unit->unit_qty = $request->get('QTY_' . $stock_no);
                            if (auth()->user()) {
                                $unit->user_id = auth()->user()->id;
                            } else {
                                $unit->user_id = 87;
                            }
                            $unit->save();
                        }
                    }
                }
            }

            return redirect()->action('LogisticsController@sku_list',
                [
                    'search_in_first' => $request->get('search_in_first'),
                    'contains_first' => $request->get('contains_first'),
                    'search_for_first' => $request->get('search_for_first'),
                    'search_in_second' => $request->get('search_in_second'),
                    'contains_second' => $request->get('contains_second'),
                    'search_for_second' => $request->get('search_for_second'),
                    'search_in_third' => $request->get('search_in_third'),
                    'contains_third' => $request->get('contains_third'),
                    'search_for_third' => $request->get('search_for_third'),
                    'search_in_fourth' => $request->get('search_in_fourth'),
                    'contains_fourth' => $request->get('contains_fourth'),
                    'search_for_fourth' => $request->get('search_for_fourth'),
                    'unassigned' => $request->get('unassigned'),
                    'stockno' => $request->get('stockno'),
                    'batch_route_id' => $request->get('batch_route_id'),
                    'sure3d' => $request->get('sure3d'),
                    'orientation' => $request->get('orientation'),
                ]);

        } else {
            return redirect()->action('LogisticsController@sku_list')->withErrors('No Child SKUs selected');
        }

    }

    public function update_ajax(Request $request)
    {
        $parameter_option = Option::with('design')
            ->where('unique_row_value', $request->get('unique_row_value'))
            ->first();

        if (!$parameter_option) {
            return 'Child SKU not found';
        }

        if (!$parameter_option->design) {
            Design::check($parameter_option->graphic_sku);
        }

        $update_flag = FALSE;

        if ($parameter_option->batch_route_id != trim($request->get('route'))) {

            $route_exists = BatchRoute::find(trim($request->get('route')));

            if ($route_exists) {
                $parameter_option->batch_route_id = trim($request->get('route'));
                $update_flag = TRUE;
            } else {
                return 'Route does not Exist';
            }
        }

        if ($parameter_option->allow_mixing != trim($request->get('mix'))) {

            $parameter_option->allow_mixing = trim($request->get('mix'));
            $update_flag = TRUE;
        }

        if ($parameter_option->graphic_sku != trim($request->get('graphic_sku'))) {
            $parameter_option->graphic_sku = trim($request->get('graphic_sku'));
            $parameter_option->save();

            Design::updateGraphicInfo(1, $parameter_option->id);

            $parameter_option = Option::where('unique_row_value', $request->get('unique_row_value'))->first();

            return 'Updated - ' . $parameter_option->design->template ? '' : ' - NoTemplate ' .
            $parameter_option->design->xml ? '' : ' - NoXML ';
        }

        if ($parameter_option->sure3d != trim($request->get('sure3d'))) {
            $parameter_option->sure3d = trim($request->get('sure3d'));
            $update_flag = TRUE;
        }


        if ( $parameter_option->orientation != trim($request->get('orientation'))) {

            $parameter_option->orientation = trim($request->get('orientation'));
            $update_flag = TRUE;
        }

        if ($parameter_option->frame_size != trim($request->get('frame_size'))) {
            $parameter_option->frame_size = trim($request->get('frame_size'));
            $update_flag = TRUE;
        }

        if ($parameter_option->mirror != trim($request->get('mirror'))) {
            $parameter_option->mirror = trim($request->get('mirror'));
            $update_flag = TRUE;
        }

        if ($update_flag) {
            $parameter_option->save();
            return 'Updated';
        } else {
            return 'No Update';
        }
    }

    public function edit_sku(Request $request)
    {
        $rules = [
            'unique_row_value' => 'required',
        ];

        $inputs = [
            'unique_row_value' => $request->get('row'),
        ];

        $validator = Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        $options = Option::where($inputs)
            ->first();

        if (!$options) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'Your input is wrong.',
                ]);
        }

        #$options = $options->toArray();
        #return $parameters;
        $batch_routes = BatchRoute::where('is_deleted', '0')
            ->orderBy('batch_route_name')
            ->get()
            ->pluck('batch_route_name', 'id');

        $data = [];
        $file = "/var/www/order.monogramonline.com/BypassOption.json";
        if(file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }
        $bypass = $data[$options['child_sku']] ?? false;

        return view('logistics.edit_sku', compact('options', 'batch_routes', 'bypass'));
    }

    public function update_sku(Request $request)
    {
// dd($request->all(), $request->stock_number);
        $rules = [
            'unique_row_value' => 'required',
        ];

        $inputs = [
            'unique_row_value' => $request->get('unique_row_value'),
        ];

        $validator = Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        $unique_row_value = $request->get('unique_row_value');

        $parent_sku = trim($request->get('parent_sku'), '');
        $graphic_sku = trim($request->get('graphic_sku'), '');
        $child_sku = trim($request->get('child_sku'), '');
        $id_catalog = trim($request->get('id_catalog'), '');
        $sure3d = trim($request->get('sure3d'), '');


        $bypassOption = (bool) trim($request->get('bypass_option'), '');

        $file = "/var/www/order.monogramonline.com/BypassOption.json";

        $data = [];
        if(file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }
        $data[$child_sku] = $bypassOption;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));


        if (empty($child_sku)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'error' => 'Child SKU or Stock Number is required',
                ]);
        }

        // todo: if child sku is changed, change on items.child_sku too

        Option::where('unique_row_value', $unique_row_value)
            ->update([
                'id_catalog' => $id_catalog,
                'parent_sku' => $parent_sku,
                'child_sku' => $child_sku,
                'graphic_sku' => $graphic_sku,
                'allow_mixing' => intval($request->get('allow_mixing', 1)),
                'batch_route_id' => intval($request->get('batch_route_id', Helper::getDefaultRouteId())),
                'sure3d' => intval($request->get('sure3d', 0)),
            ]);


        return redirect()
            ->action('LogisticsController@sku_list', ['search_for_first' => $child_sku, 'search_in_first' => 'child_sku'])
            ->with('success', "Data updated.");
    }

    public function get_add_child_sku(Request $request)
    {
        $parameters = Parameter::where('is_deleted', '0')
            ->get();

        $batch_routes = BatchRoute::where('is_deleted', '0')
            ->orderBy('batch_route_name')
            ->get()
            ->pluck('batch_route_name', 'id');

        return view('logistics.add_child_sku', compact('parameters', 'batch_routes', 'request'));
    }

    public function post_add_child_sku(Request $request)
    {
        $rules = [
            'child_sku' => 'required',
        ];

        $inputs = [
            'child_sku' => $request->get('child_sku'),
        ];

        $validator = Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        $child_sku = Option::where('child_sku', $request->get('child_sku'))->first();

        if ($child_sku) {
            return redirect()->back()->withInput()->withErrors('Child SKU ' . $request->get('child_sku') . ' already exists');
        }

        $unique_row_value = Helper::generateUniqueRowId();

        $parameters = Parameter::where('is_deleted', '0')
            ->get();

        if ($parameters->count() == 0) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'No Parameters available.',
                ]);
        }

        // check if the code is found on request
        // match, if the code found
        // update the value
        $is_code_field_found = false;
        $code = '';
        $dataToStore = [];
        foreach ($parameters as $parameter) {
            $parameter_value = $parameter->parameter_value;
            $form_field = Helper::textToHTMLFormName($parameter_value);
            if ($form_field == 'code') {
                $is_code_field_found = true;
                $code = $request->get($form_field, '');
            }
            $dataToStore[$parameter_value] = $request->get($form_field, '');
        }
        // check if the code is already existing on database or not
        $option = null;

        $parent_sku = trim($request->get('parent_sku'), '');
        $graphic_sku = trim($request->get('graphic_sku'), '');
        $child_sku = trim($request->get('child_sku'), '');
        $id_catalog = trim($request->get('id_catalog'), '');

        if ($is_code_field_found) {
            $option = Option::where('child_sku', $child_sku)
                ->first();
        }

        if (!$option) {
            $option = new Option();
            $option->unique_row_value = $unique_row_value;
            $option->child_sku = $child_sku;

        }

        $option->parent_sku = $parent_sku;
        $option->graphic_sku = $graphic_sku;
        $option->id_catalog = $id_catalog;
        $option->allow_mixing = intval($request->get('allow_mixing', 1));
        $option->batch_route_id = intval($request->get('batch_route_id', Helper::getDefaultRouteId()));
        $option->parameter_option = json_encode($dataToStore);
        $option->sure3d = intval($request->get('sure3d', 0));
        $option->orientation = 0;

        $option->save();

        return redirect()
            ->action('LogisticsController@sku_list', ['search_for_first' => $child_sku, 'search_in_first' => 'child_sku'])
            ->with('success', "Child sku inserted.");
    }

    public function create_child_sku(Request $request)
    {
        $id_catalog = trim($request->get('id_catalog', null));

        $crawled_data = null;
        if ($id_catalog) {
            $json = Crawler::getJSON($id_catalog);

            $crawled_data = json_decode($json, true);
        }

        $parameters = Parameter::get()->pluck('parameter_value')->toArray();
        $parameters = array_map('strtolower', $parameters);

        return view('logistics.create_child_sku')
            ->with('id_catalog', $id_catalog)
            ->with('crawled_data', $crawled_data)
            ->with('parameters', $parameters);
    }

    public function post_create_child_sku(Request $request)
    {

        $id_catalog = $request->get('id_catalog');
        $product = Product::where('id_catalog', $id_catalog)
            ->first();
        if (!$product) {
            return redirect()
                ->back()
                ->with([
                    'error' => 'No product found in database with this id catalog.',
                ]);
        }
        $available_groups = $request->get('groups', []);
        $checked_group_values = [];
        $selected_groups = [];
        foreach ($available_groups as $group) {
            $selected = $request->get($group, []);
            if ($selected) {
                $checked_group_values[] = $selected;
                $selected_groups[] = $group;
            }
        }
        if (count($checked_group_values) == 0) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'No group is selected to create a preview.',
                ]);
        }
        $suggestions = Helper::generateChildSKUCombination($checked_group_values);

        return view('logistics.preview_child_sku')
            ->with('suggestions', $suggestions)
            ->with('id_catalog', $id_catalog)
            ->with('product', $product)
            ->with('selected_groups', $selected_groups)
            ->with('checked_group_values', $checked_group_values);
    }

    public function post_preview(Request $request)
    {
        // selected-options[] = if the checkbox is selected, then the child sku will be created
        // and the value will be in this field

        // selected-group[] = the selected groups to create the child sku

        // selected-child-sku[] = the selected child skus from the suggestions, with or without edit

        $selected_groups = $request->get('selected-group');
        $selected_options = $request->get('selected-options');
        $selected_child_sku_suggestions = $request->get('selected-child-sku');
        if (count($selected_child_sku_suggestions) == 0) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'No child SKU is selected to be added',
                ]);
        }

        $parent_sku = $request->get('parent_sku');
        $id_catalog = $request->get('id_catalog');
        // $store_id = $request->get('store');
        // $store = Store::where('is_deleted', 0)
        // 			  ->find($store_id);
        // if ( !$store ) {
        // 	return redirect()
        // 		->back()
        // 		->withErrors([
        // 			'error' => 'Not a valid store is chosen.',
        // 		]);
        // }

        if (empty($parent_sku)) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'No product is available with that id catalog.',
                ]);
        }
        // insert the column values that are not in the parameters table
        // insert into that table.

        // $available_groups = Parameter::get()->pluck('parameter_value');
        // $not_available = array_diff($selected_groups, $available_groups);

// 		foreach ( $not_available as $inserable ) {
// 			$parameter = new Parameter();
// 			$parameter->store_id = $store->store_id;
// 			$parameter->parameter_value = $inserable;
// 			$parameter->save();
// 		}

        $batch_route_id = Helper::getDefaultRouteId();
        $rows = [];
        $index = 0;
        foreach ($selected_options as $option) {
            $options_array = json_decode($option);
            $combined_array = array_combine($selected_groups, $options_array);
            $child_sku = $selected_child_sku_suggestions[$index];

            $rows[] = [
                //'store_id'         => $store->store_id,
                'unique_row_value' => Helper::generateUniqueRowId(),
                'id_catalog' => $id_catalog,
                'graphic_sku' => 'NeedGraphicFile',
                'parent_sku' => $parent_sku,
                'allow_mixing' => 0,
                'batch_route_id' => $batch_route_id,
                'child_sku' => $child_sku,
                'parameter_option' => json_encode($combined_array),
                'sure3d' => 0,
            ];
            ++$index;
        }

        foreach ($rows as $columns) {
            $option = Option::where('child_sku', $columns['child_sku'])
                ->first();
            if (!$option) {
                $option = new Option();

                foreach ($columns as $column_key => $column_value) {
                    $option->$column_key = $column_value;
                }

                $option->save();
            }
        }

        return redirect()->to(url(sprintf("/logistics/sku_list?search_for_first=%s&search_in_first=parent_sku", $parent_sku)));
    }

    private function get_unique_id()
    {
        return sprintf("%s_%s", strtotime("now"), str_random(5));
    }

}
