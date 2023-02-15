<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Monogram\AppMailer;
use App\PickingReport;
use App\Batch;

class PickingController extends Controller
{
  public function getInventorySummary (Request $request) {

    $items_1 = Batch::with('production_station')
          ->join('items', 'batches.batch_number', '=', 'items.batch_number')
          ->join('stations', 'batches.production_station_id', '=', 'stations.id')
          ->leftjoin('sections', 'stations.section', '=', 'sections.id')
          ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
          ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
          ->selectRaw('sections.section_name, sections.id as section_id, batches.production_station_id,
                    batches.station_id, inventories.stock_no_unique, inventories.wh_bin, stations.type,
                    inventories.stock_name_discription, SUM(items.item_quantity) as total')
          ->searchStatus('active')
          ->where('batches.inventory', '0')
          ->whereNull('picking_report_id')
          ->where('stations.type', '!=', 'Q')
          ->whereIn('sections.inventory',['1','2'])
          ->where('items.item_status', '1')
          // ->where(function ($query) use () {
          //       $query->where('stations.type', 'G')
          //             ->orWhere('batches.station_id', '=', DB::raw('batches.production_station_id')); dd($query->toSql());
          //   })
          ->groupBy('stations.section', 'batches.production_station_id', \DB::raw('inventories.stock_no_unique'))
          ->orderBy('sections.section_name', 'ASC')
          ->orderBy('production_station_id', 'ASC')
          ->orderBy('wh_bin', 'ASC')
          ->get();
    
      $items_2 = Batch::with('store', 'production_station')
            ->join('items', 'batches.batch_number', '=', 'items.batch_number')
            ->join('stations', 'batches.production_station_id', '=', 'stations.id')
            ->leftjoin('sections', 'stations.section', '=', 'sections.id')
            ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
            ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
            ->selectRaw('sections.section_name, sections.id as section_id, batches.store_id, batches.batch_number,
                      inventories.stock_no_unique, inventories.wh_bin, stations.type,
                      inventories.stock_name_discription, SUM(items.item_quantity) as total')
            ->searchStatus('active')
            ->where('batches.inventory', '0')
            ->whereNull('picking_report_id')
            ->where('stations.type', '!=', 'Q')
            ->where('sections.inventory','3')
            ->where('items.item_status', '1')
            ->groupBy('batches.batch_number')
            ->groupBy('inventory_unit.stock_no_unique')
            ->orderBy('sections.section_name', 'ASC')
            ->orderBy('batches.batch_number', 'ASC')
            ->orderBy('wh_bin', 'ASC')
            ->get(); 
            
    $picking = Batch::with('section', 'picking_report.picking_user', 'store')
              ->join('picking_reports', 'batches.picking_report_id', '=', 'picking_reports.id')
              ->where('batches.inventory', 1)
              ->whereNotNull('batches.picking_report_id')
              ->whereNull('picking_reports.picked_date')
              ->groupby('picking_reports.id')
              ->orderBy('picking_reports.picking_date', 'ASC')
              ->get();
    
    $date  = date("Y-m-d") . ' 00:00:00';
    
    $picked = Batch::with('section', 'picking_report.picked_user', 'store')
              ->join('picking_reports', 'batches.picking_report_id', '=', 'picking_reports.id')
              ->where('batches.inventory', 2) 
              ->whereNotNull('batches.picking_report_id')
              ->where('picking_reports.picked_date', '>', $date)
              ->groupby('picking_reports.id')
              ->orderBy('picking_reports.picked_date', 'ASC')
              ->get();

      return view ( 'picking.summary', compact ( 'items_1', 'items_2', 'unassigned', 'picking', 'picked', 'pick_by_store' ));
  }
  
  public function printInventoryReport (Request $request) {
      
      $section_name = $request->get('section_name');
      $section_id = $request->get('section_id');
      $batch_number = $request->get('batch_number');
      
      $report_date =  date("Y-m-d  H:i:s"); 
      
      if (!$request->has('picking_report')) {
        
          if (!$request->has('section_id') || !$request->has('section_name')) {
            Log::error('printInventorySummary: Section Info not passed');
            return 'No Department Information';
          } 
          
          if ($batch_number == null) {
            $batch_list = Batch::leftjoin('stations', 'batches.production_station_id', '=', 'stations.id')
                ->searchStatus('active')
                ->where('batches.inventory', 0)
                ->whereNull('picking_report_id')
                ->where('section_id', $section_id)
                ->where('stations.type', '!=', 'Q')
                // ->where(function ($query) {
                //       $query->where('stations.type', 'G')
                //             ->orWhere('batches.station_id', '=', DB::raw('batches.production_station_id'));
                //   })
                ->get()
                ->pluck('batch_number');
          } else {
            $batch_list = [$batch_number];
          }
          
          $report = new PickingReport;
          $report->picking_date = $report_date;
          $report->picking_user_id = auth()->user()->id;
          $report->batch_number = $batch_number;
          $report->save();
          
          $report_id = $report->id;
          
          Batch::whereIn('batch_number', $batch_list)
                ->update([
                  'inventory'	=> 1,
                  'picking_report_id'	=> $report_id,
                ]);
              
      } else {
        
        $batch_list = Batch::where('picking_report_id', $request->get('picking_report'))
            ->get()
            ->pluck('batch_number');
      
        $report_id = $request->get('picking_report');
      }
      
      $item = Batch::with('production_station', 'picking_report.picking_user', 'store')
        ->join('items', 'batches.batch_number', '=', 'items.batch_number')
        ->join('stations', 'batches.production_station_id', '=', 'stations.id')
        ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
        ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
        ->selectRaw('batches.picking_report_id,	batches.production_station_id, inventories.stock_no_unique, batches.store_id, 
                inventories.wh_bin, inventories.stock_name_discription, SUM(items.item_quantity) as total')
        ->whereIn('batches.batch_number', $batch_list)
        ->where('items.item_status', '1')
        ->groupBy('batches.production_station_id', \DB::raw('inventories.stock_no_unique'))
        ->orderBy('production_station_id', 'ASC')
        ->orderBy('wh_bin', 'ASC')
        ->get();
      
      $unassigned = Batch::with('production_station', 'picking_report.picking_user', 'store')
        ->join('items', 'batches.batch_number', '=', 'items.batch_number')
        ->join('stations', 'batches.production_station_id', '=', 'stations.id')
        ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
        ->selectRaw('batches.picking_report_id,	batches.production_station_id, SUM(items.item_quantity) as total, batches.store_id,
                      inventory_unit.stock_no_unique, items.item_code, items.child_sku, items.item_description')
        ->whereIn('batches.batch_number', $batch_list)
        ->where('items.item_status', '1')
        ->where('inventory_unit.stock_no_unique', 'ToBeAssigned')
        ->groupBy('batches.production_station_id', 'items.item_code', 'items.child_sku')
        ->orderBy('production_station_id', 'ASC')
        ->orderBy('items.item_code', 'ASC')
        ->get();
        
      return view ( 'picking.inventory_summary', compact ( 'item', 'unassigned', 'section_name', 'section_id', 
                                                          'report_id', 'report_date', 'batch_number' ));
  }
  
  public function viewInventoryReport (Request $request) {
    
    if (!$request->has('picking_report')) {
      Log::error('viewInventoryReport: Report Info not passed');
      return 'No Report Information';
    } 
    
    if (substr( trim(  $request->get('picking_report')) , 0, 4) == 'INVT') {
      $picking_report = substr( trim(  $request->get('picking_report')), 4);
    } else {
      $picking_report = trim( $request->get('picking_report'));
    }
    
    $report = PickingReport::with('batches.section', 'picking_user', 'picked_user')
              ->where('id', $picking_report)
              ->first();
    
    if (count($report) == 0) {
      return redirect()->action('PickingController@getInventorySummary')->withErrors('Report Not Found');
    }
      
    $picking_date = $report->picking_date;
    $section_name = $report->batches->first()->section->section_name;
    $picking_user = $report->picking_user->username;
    $picked_date = $report->picked_date;
    $batch_number = $report->batch_number;
    
    if (isset($report->picked_user)) {
      $picked_user = $report->picked_user->username;
    } else {
      $picked_user = '';
    }
      
    if ($request->has('task')) {
      $task = $request->get('task');
    } elseif ($picked_date == NULL) {
      $task = 'pick';
    } else {
      $task = 'view';
    }
    
    $item = Batch::with('production_station')
      ->join('items', 'batches.batch_number', '=', 'items.batch_number')
      ->join('stations', 'batches.production_station_id', '=', 'stations.id')
      ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
      ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
      ->selectRaw('batches.picking_report_id, batches.production_station_id, inventories.stock_no_unique, 
              inventories.wh_bin, inventories.stock_name_discription, SUM(items.item_quantity) as total')
      ->where('batches.picking_report_id', $picking_report)
      ->where('items.item_status', '1')
      ->groupBy('batches.production_station_id', \DB::raw('inventories.stock_no_unique'))
      ->orderBy('production_station_id', 'ASC')
      ->orderBy('wh_bin', 'ASC')
      ->get();

      $unassigned = Batch::with('production_station')
        ->join('items', 'batches.batch_number', '=', 'items.batch_number')
        ->join('stations', 'batches.production_station_id', '=', 'stations.id')
        ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
        ->selectRaw('batches.picking_report_id, batches.production_station_id, inventory_unit.stock_no_unique, 
                items.item_code, items.item_description, SUM(items.item_quantity) as total')
        ->where('batches.picking_report_id', $picking_report)
        ->where('items.item_status', '1')
        ->where('inventory_unit.stock_no_unique', 'ToBeAssigned')
        ->groupBy('batches.production_station_id', 'items.item_code')
        ->orderBy('production_station_id', 'ASC')
        ->get();

    return view ( 'picking.picking_report', compact ( 'item', 'unassigned', 'picking_report', 'section_name', 'task', 'picking_report', 
                                        'picking_date', 'picking_user', 'picked_date', 'picked_user', 'batch_number' ));
  }	
  
  public function pickInventoryReport (Request $request) {
    
    if (!$request->has('picking_report') || !$request->has('key')) {
      Log::error('pickInventoryReport: Report Info not passed');
      return 'No Report Information';
    } 
    
    if (substr( trim(  $request->get('picking_report')) , 0, 4) == 'INVT') {
      $picking_report = substr( trim(  $request->get('picking_report')), 4);
    } else {
      $picking_report = trim( $request->get('picking_report'));
    }

    $pick_date = date("Y-m-d  H:i:s");
    $user = auth()->user()->id;

    foreach ($request->get('key') as $key) {
      
      $required = $request->get($key . '*required');
      $picked = $request->get($key . '*picked');
      
      if ($required != $picked) {
        
        $exploded = explode('*^*', $key);
        // $production_station = $exploded[1];
        $stockno = $exploded[0];

        AppMailer::sendMessage('noreply@monogramonline.com', 'Inventory System', 'anthony@monogramonline.com', 
                'Inventory Adjustment Required: Stock No ' . $stockno , 
                'Quantity of Stock No ' . $stockno . ' required for Inventory Summary # ' . $picking_report . ' was not found.'  . "\r\n" .
                '( ' . $required . ' required, ' .  $picked . ' picked )');
                
        Log::info('pickInventoryReport: Inventory Adjustment Required: Stock No ' . $stockno . ' picking report ' . $picking_report);
      }
      
    }
    
    $batches = Batch::with('section')
                      ->where('picking_report_id', $picking_report)
                      ->get();
                      
    foreach ($batches as $batch) {
      
      $batch->inventory = 2;
      
      if ($batch->section->inventory == '3') {
        $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);
        if ($next_station && $next_station->id != '0') { 
          $batch->prev_station_id = $batch->station_id; 
          $batch->station_id = $next_station->id; 
          $batch->save(); 
          $success = sprintf('Batch %s Successfully Moved to %s<br>', $batch->batch_number,$next_station->station_name); 
        } else { 
          Log::error('pickInventoryReport: Batch ' . $batch->batch_number . ' has no further stations on route'); 
        } 
      }
      
      $batch->save();
    }
    
    PickingReport::where('id', $picking_report)
              ->update([
                'picked_date'	=> $pick_date,
                'picked_user_id'	=> $user,
              ]);
              
    return redirect()->action('PickingController@getInventorySummary');
  }	
  
  public function deleteInventoryReport (Request $request) {
    
    if (!$request->has('picking_report')) {
      Log::error('deleteInventoryReport: Report Info not passed');
      return 'No Report Information';
    } 
    
    if (substr( trim(  $request->get('picking_report')) , 0, 4) == 'INVT') {
      $picking_report = substr( trim(  $request->get('picking_report')), 4);
    } else {
      $picking_report = trim( $request->get('picking_report'));
    }
    
    $report = PickingReport::find($picking_report);
              
    $batches = Batch::where('picking_report_id', $picking_report)->get();
                      
    foreach ($batches as $batch) {
      $batch->picking_report_id = null;
      $batch->inventory = '0';
      $batch->save();
    }
    
    $report->is_deleted = '1';
    $report->save();
    
    return redirect()->action('PickingController@getInventorySummary');
    
  }
  
}
