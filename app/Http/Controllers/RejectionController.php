<?php

namespace App\Http\Controllers;

use App\Store;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Item;
use App\InventoryAdjustment;
use App\Order;
use App\Ship;
use App\Rejection;
use App\RejectionReason;
use App\Batch;
use App\Wap;
use App\Section;
use App\WapItem;
use Monogram\Helper;
use Monogram\Batching;
use App\Http\Controllers\GraphicsController;

class RejectionController extends Controller
{

    public $archive = '/media/RDrive/archive/';
    protected $remotArchiveUrl = "https://order.monogramonline.com/media/archive/";
    public function index (Request $request)
    {   
        $batch_array = array();
        $store_ids = Store::where('permit_users', 'like', "%".auth()->user()->id ."%")
            ->where('is_deleted', '0')
            ->where('invisible', '0')
            ->get()
            ->pluck('store_id')
            ->toArray();
        
        if ($request->all() == []) {
          
          $summary = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
                    ->where ( 'items.is_deleted', '0' )
          					->searchStatus ( 'rejected' )
                    ->where('graphic_status', '!=', 4)
                    ->whereIn('store_id', $store_ids)
                    ->where('rejections.complete', '0')
                    ->selectRaw('rejections.graphic_status, rejections.rejection_reason, COUNT(items.id) as count')
                    ->groupBy('rejections.graphic_status')
                    ->groupBy('rejections.rejection_reason')
                    ->orderBy('rejections.graphic_status')
                    ->orderBy('rejections.rejection_reason')
          					->get();

        } else {
          
          if ($request->has('label')) {
            $label = $request->get('label');
          } else {
            $label = null;
          }
          
          $items = Item::with ( 'rejection.rejection_reason_info', 'rejection.user', 'rejection.from_station', 'rejections', 
                                'order', 'batch' )
                    ->where ( 'is_deleted', '0' )
                    ->whereIn('store_id', $store_ids)
          					->searchStatus ( 'rejected' )
                    ->searchBatch(trim($request->get('batch_number')))
                    ->searchGraphicStatus($request->get('graphic_status'))
                    ->searchSection($request->get('section'))
                    ->searchRejectReason( $request->get('reason') )
                    ->orderBy('batch_number', 'ASC')
          					->get();
          
          $total_items = count($items);
          
      		foreach ($items as $item) {
            
            if (!array_key_exists($item->batch_number, $batch_array)) {
              $batch_array[$item->batch_number]['items'] = $items->where('batch_number', $item->batch_number)->all();
              $batch_array[$item->batch_number]['summaries'] = $item->batch->summary_count;
              $batch_array[$item->batch_number]['id'] = $item->batch->id;
            }
      		}
        }
    		
        $graphic_statuses = Rejection::graphicStatus(1);
        
        $destinations = ['0' => 'Send Batch to', 'G' => 'Graphics', 'GM' => 'Manual Graphics', 'P' => 'Production', 'Q' => 'Quality Control'];
        
        $sections = Section::where('is_deleted', '0')->get()->pluck('section_name', 'id')->prepend('Select a Department', '');
        
        $reasons = RejectionReason::getReasons();
        
    		return view ( 'rejections.index', compact ( 'batch_array', 'total_items', 'destinations', 'graphic_statuses', 'label', 
                                                    'summaries', 'sections', 'reasons', 'request', 'summary') );

    }

    
    public function process (Request $request) {
      
      $error = array();
      $success = array();
      
      foreach ($request->get('supervisor_message') as $id => $msg) {
        
        if ($msg != '') {
          $record = Rejection::find($id);
          
          $record->supervisor_message = $record->supervisor_message . ' ' . $msg;
          $record->save();
        }
      }
      
      if ($request->get('station_change') != '0') { 
      
        $result = $this->moveStation($request->get('batch_number'), substr($request->get('station_change'), 0, 1));
        
        if ($result) {
          $success[] = 'Batch ' . $request->get('batch_number') . ' Moved';
        } else {
          $error[] = 'Error moving '  . $request->get('batch_number');
        }
      }
      
      if ($request->get('station_change') == 'G') {
        
        $msg = Batch::export($request->get('batch_number'), '0');
        
        if (isset($msg['success'])) {
          $success[] = $msg['success'];
        }
        
        if (isset($msg['error'])) {
          $error[] = $msg['error'];
        }
        
      } else if ($request->get('station_change') == 'GM') {
        
        touch(GraphicsController::$manual_dir . $request->get('batch_number') . '.csv');
        $success[] = 'Batch ' . $request->get('batch_number') . ' sent to Manual Graphics';
        
      }
      
      return redirect()->action('RejectionController@index', 
                    ['graphic_status' => $request->get('graphic_status'), 'section' => $request->get('section'), 'reason' => $request->get('reason')])
              ->with('success', $success)
              ->withErrors($error);
    }
    
    
    public function sendToStart (Request $request) {
      
      $batch_numbers = explode(',', $request->get('batches'));
      
      $error = array();
      $success = array();
      
      foreach ($batch_numbers as $batch_number) {
        
        $result = $this->moveStation($batch_number);
        
        if ($result) {
          $success[] = 'Batch ' . $batch_number . ' Moved to Production';
        } else {
          $error[] = 'Error moving '  . $batch_number;
        }
        
        $msg = Batch::export($batch_number, '0');
        
        if (isset($msg['success'])) {
          $success[] = $msg['success'];
        }
        
        if (isset($msg['error'])) {
          $error[] = $msg['error'];
        }
      }
      
      return redirect()->action('RejectionController@index', ['graphic_status' => $request->get('graphic_status'), 'section' => $request->get('section')])
              ->with('success', $success)
              ->withErrors($error);
    }
    
    private function moveStation ($batch_number, $station_change = null) {
      
      $batch = Batch::with('route.stations_list')
                ->where('batch_number', $batch_number)
                ->first();
      
      if (count($batch) < 1) {
        return false;
        
      } else {
          
          if ($station_change == null || $station_change == 'G') { 
            $station_change = $batch->route->stations_list->first()->station_id; 
          } else if ($station_change == 'P') { 
            $station_change = $batch->route->production_stations->first()->id; 
          } else if ($station_change == 'Q') { 
            $station_change = $batch->route->qc_stations->first()->id;
          }

//          dd($station_change);

          $batch->prev_station_id = $batch->station_id;
          $batch->station_id = $station_change;
          $batch->status = 'active';
          $batch->save();
          
          $items = Item::where('batch_number', $batch->batch_number)
                          ->where('is_deleted', '0')
                          ->get();
          
          foreach($items as $item) {
            $item->item_status = 1;
            $item->save();
          }
          
          Rejection::where('to_batch', $batch->batch_number)
                ->whereNull('to_station_id')
                ->update([
                  'supervisor_user_id' => auth()->user()->id,
                  'to_station_id'      => $station_change,
                  'complete'           => '1'
                ]);
          
          Batch::note($batch->batch_number,$batch->station_id,'5','Reject batch moved into production');
          
          return true;
      }
    }
    
    public function csProcess (Request $request) {
      
      $error = array();
      $success = array();
      
      foreach ($request->get('supervisor_message') as $id => $msg) {

        $record = Rejection::find($id);
        
        $record->supervisor_message = $record->supervisor_message . ' ' . $msg;
        $record->save();
      }

      if ($request->get('solved') == 'on') {
        
        $batch = Batch::with('items.rejection')
                  ->where('batch_number', $request->get('batch_number'))
                  ->first();
        
        if (count($batch) < 1) {
          
          $error[] = 'Batch ' . $request->get('batch_number') . ' Not Found';
          
        } else {
          
            foreach ($batch->items as $item) {
              if ($item->rejection) {
                $item->rejection->graphic_status = 5;
                $item->rejection->save();
              }
            }
            
            $success[] = 'Batch ' . $request->get('batch_number') . ' Moved';
            Batch::note($batch->batch_number,$batch->station_id,'5','Customer Service Issue Solved');
        }        
      }
      
      return redirect()->action('CsController@index')
              ->with('success', $success)
              ->withErrors($error)
              ->with('tab', 'rejects');
    }

    public function rejectAndArchive(Request $request)
    {
        logger('Reject and Archive processing started..');
        try {
            $origin = $request->get('origin');

            $rules = [
                'item_id'           => 'required',
                'reject_qty'        => 'required|integer|min:1',
                'graphic_status'    => 'required',
                'rejection_reason'  => 'required|exists:rejection_reasons,id',
            ];

            $validation = Validator::make($request->all(), $rules);

            if ( $validation->fails() ) {
                return redirect()->back()->withErrors($validation);
            }

            $item = Item::with('inventoryunit')
                ->where('id', $request->get('item_id'))
                ->first();
            $item->vendor = null;

            if ($item->item_status == 'rejected') {
                return redirect()->back()->withErrors(['error' => 'Item Already Rejected']);
            }

            if($request->rejection_reason == 117 && $request->graphic_status == '1') {
                $batch = Batch::with('route.stations_list')->where('batch_number', $item->batch_number)
                    ->first();

                $option = json_decode($item->item_option);
                $image_name = basename($option->Custom_EPS_download_link);


                // Use parse_url to get the path
                $path = parse_url($image_name, PHP_URL_PATH);

                // Use pathinfo to get the filename
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $fileExt = pathinfo($path, PATHINFO_EXTENSION);

                $originalFilePath = $this->archive . $filename. '.' .$fileExt;
                $newFilePath =  str_replace($filename, $item->batch_number, $originalFilePath);

                if($originalFilePath == $newFilePath && file_exists($originalFilePath)){
                    if(!empty($batch)){
                        $batch->prev_station_id = $batch->station_id;
                        $batch->station_id = 92; //Set Graphics done S-GRPH
                        $batch->graphic_found = '1';
                        $batch->save();

                        $item->save();

                        logger('now moving for re print graphic...');
                        return redirect()->action('GraphicsController@reprintGraphic',[
                            'name' => $request->get('name'),
                            'directory' => $request->get('directory'),
                            'goto' => $request->get('goto'),
                        ]);

//                        return redirect()->back()->withSuccess('Batch moved to Graphics done');
                    } else {
                        return redirect()->back()->withErrors(['error' => 'Batch not found']);
                    }
                } else if(file_exists($originalFilePath) && !file_exists($newFilePath)) {
                    copy($originalFilePath, $newFilePath);
                    unlink($originalFilePath);

                    logger("Command successfully execute and changed the file : " . $newFilePath);

                    $option->Custom_EPS_download_link = $this->remotArchiveUrl . $item->batch_number . '.' . $fileExt;
                    $item->item_option = json_encode($option);
                    $item->save();

                    if(!empty($batch)){
                        $batch->prev_station_id = $batch->station_id;
                        $batch->station_id = 92; //Set Graphics done S-GRPH
                        $batch->graphic_found = '1';
                        $batch->save();

                        logger('now moving for re print graphic...');
                        return redirect()->action('GraphicsController@reprintGraphic',[
                            'name' => $request->get('name'),
                            'directory' => $request->get('directory'),
                            'goto' => $request->get('goto'),
                        ]);
//                        return redirect()->back()->withSuccess('Batch moved to Graphics done');

                    } else {
                        return redirect()->back()->withErrors(['error' => 'Batch not found']);
                    }
                } else {
                    return redirect()->back()->withErrors(['error' => 'File not found']);
                }
            }
            $batch_number = $item->batch_number;

            if ($origin == 'QC') {
                $batch = Batch::find($request->get('id'));

                if ($batch && $batch->batch_number != $batch_number) {
                    return redirect()->back()->withErrors(['error' => sprintf('Please scan user ID')]);
                }
            } elseif ($origin == 'SL') {
                $tracking_number = $item->tracking_number;
            }

            $result = $this->itemReject($item, $request->get('reject_qty'), $request->get('graphic_status'), $request->get('rejection_reason'),
                $request->get('rejection_message'), $request->get('title'), $request->get('scrap'));

            //send first reprint to production
            if ($request->get('graphic_status') == '1') {

                $count = Rejection::where('item_id', $result['reject_id'])->count();
                $batch = Batch::with('route.stations_list')
                    ->where('batch_number', $batch_number)
                    ->first();

                $station_change = $batch->route->stations_list[1]->station_id;

                if ($count == 1) {
                    $this->moveStation($result['new_batch_number'], $station_change);

                    $msg = Batch::export($result['new_batch_number'], '0');

                    if (isset($msg['error'])) {
                        Batch::note( $result['new_batch_number'], '', 0, $msg['error']);
                    }
                }
            }
            logger('now moving for re print graphic...');
            return redirect()->action('GraphicsController@reprintGraphic',[
                'name' => $request->get('name'),
                'directory' => $request->get('directory'),
                'goto' => $request->get('goto'),
            ]);

        } catch (\Exception $e) {
            logger('Error in reject: ' . $e->getMessage());
            logger([
                'error' => $e->getMessage(),
                'function' =>   __FUNCTION__,
                'line' => __LINE__
            ]);
        }
    }

    
    public function reject (Request $request) {
        try {
            $origin = $request->get('origin');

            $rules = [
                'item_id'           => 'required',
                'reject_qty'        => 'required|integer|min:1',
                'graphic_status'    => 'required',
                'rejection_reason'  => 'required|exists:rejection_reasons,id',
            ];

            $validation = Validator::make($request->all(), $rules);

            if ( $validation->fails() ) {
                return redirect()->back()->withErrors($validation);
            }

            $item = Item::with('inventoryunit')
                ->where('id', $request->get('item_id'))
                ->first();
            $item->vendor = null;

            if ($item->item_status == 'rejected') {
                return redirect()->back()->withErrors(['error' => 'Item Already Rejected']);
            }

            if($request->rejection_reason == 117 && $request->graphic_status == '1') {
                $batch = Batch::with('route.stations_list')->where('batch_number', $item->batch_number)
                    ->first();

                $option = json_decode($item->item_option);
                $image_name = basename($option->Custom_EPS_download_link);


                // Use parse_url to get the path
                $path = parse_url($image_name, PHP_URL_PATH);

                // Use pathinfo to get the filename
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $fileExt = pathinfo($path, PATHINFO_EXTENSION);

                $originalFilePath = $this->archive . $filename. '.' .$fileExt;
                $newFilePath =  str_replace($filename, $item->batch_number, $originalFilePath);

                if($originalFilePath == $newFilePath && file_exists($originalFilePath)){
                    if(!empty($batch)){
                        $batch->prev_station_id = $batch->station_id;
                        $batch->station_id = 92; //Set Graphics done S-GRPH
                        $batch->graphic_found = '1';
                        $batch->save();

                        $item->save();

                        return redirect()->back()->withSuccess('Batch moved to Graphics done');
                    } else {
                        return redirect()->back()->withErrors(['error' => 'Batch not found']);
                    }
                } else if(file_exists($originalFilePath) && !file_exists($newFilePath)) {
                    copy($originalFilePath, $newFilePath);
                    unlink($originalFilePath);

                    logger("Command successfully execute and changed the file : " . $newFilePath);

                    $option->Custom_EPS_download_link = $this->remotArchiveUrl . $item->batch_number . '.' . $fileExt;
                    $item->item_option = json_encode($option);
                    $item->save();

                    if(!empty($batch)){
                        $batch->prev_station_id = $batch->station_id;
                        $batch->station_id = 92; //Set Graphics done S-GRPH
                        $batch->graphic_found = '1';
                        $batch->save();

                        return redirect()->back()->withSuccess('Batch moved to Graphics done');
                    } else {
                        return redirect()->back()->withErrors(['error' => 'Batch not found']);
                    }
                } else {
                    return redirect()->back()->withErrors(['error' => 'File not found']);
                }
            }
            $batch_number = $item->batch_number;

            if ($origin == 'QC') {
                $batch = Batch::find($request->get('id'));

                if ($batch && $batch->batch_number != $batch_number) {
                    return redirect()->back()->withErrors(['error' => sprintf('Please scan user ID')]);
                }
            } elseif ($origin == 'SL') {
                $tracking_number = $item->tracking_number;
            }

            $result = $this->itemReject($item, $request->get('reject_qty'), $request->get('graphic_status'), $request->get('rejection_reason'),
                $request->get('rejection_message'), $request->get('title'), $request->get('scrap'));

            //send first reprint to production
            if ($request->get('graphic_status') == '1') {

                $count = Rejection::where('item_id', $result['reject_id'])->count();
                $batch = Batch::with('route.stations_list')
                    ->where('batch_number', $batch_number)
                    ->first();

                $station_change = $batch->route->stations_list[1]->station_id;

                if ($count == 1) {
                    $this->moveStation($result['new_batch_number'], $station_change);

                    $msg = Batch::export($result['new_batch_number'], '0');

                    if (isset($msg['error'])) {
                        Batch::note( $result['new_batch_number'], '', 0, $msg['error']);
                    }
                }
            }

            if ($origin == 'QC') {

                return redirect()->route('qcShow', ['id' => $request->get('id'), 'batch_number' => $batch_number, 'label' => $result['label']]);

            } elseif ($origin == 'BD') {

                return redirect()->route('batchShow', ['batch_number' => $batch_number, 'label' => $result['label']]);

            } elseif ($origin == 'WP') {

                return redirect()->route('wapShow', ['bin' => $request->get('bin_id'), 'label' => $result['label'], 'show_ship' => '1']);

            } elseif ($origin == 'SL') {

                $order = Order::find($item->order_5p);

                $order->order_status = 4;
                $order->save();

                Order::note("Order status changed from Shipped to To Be Processed - Item $item->id rejected after shipping", $order->order_5p, $order->order_id);

                $shipment = Ship::with('items')
                    ->where('order_number', $order->id)
                    ->where('tracking_number', $tracking_number)
                    ->first();

                if ($shipment && $shipment->items && count($shipment->items) == 0) {
                    $shipment->delete();
                }

                return redirect()->route('shipShow', ['search_for_first' => $tracking_number, 'search_in_first' => 'tracking_number', 'label' => $result['label']]);

            } elseif ($origin == 'MP') {

                return redirect()->action('GraphicsController@showBatch', ['scan_batches' => $request->get('scan_batches')]);

            } else {

                $label = $result['label'];
                return view ('prints.includes.label', compact('label'));
            }
        } catch (\Exception $e) {
            logger('Error in reject: ' . $e->getMessage());
            logger([
              'error' => $e->getMessage(),
                'function' =>   __FUNCTION__,
                'line' => __LINE__
            ]);
        }
    }
    
    public function redoItem (Request $request) {
      
      if (!$request->has('item_id')) {
        return redirect()->back()->withErrors(['error' => 'Item not set']);
      }
      
      $item = Item::with('inventoryunit', 'parameter_option', 'store')
                    ->where('id', $request->get('item_id'))
                    ->first();
      
      if (!$item->parameter_option) {
        return redirect()->back()->withErrors(['error' => 'Child SKU doesn not exist']);
      }
      
      if ($item->parameter_option->batch_route_id == 115) {
        return redirect()->back()->withErrors(['error' => 'Child SKU is not configured']);
      }
      
      if ($item->item_status == 'rejected') {
        return redirect()->back()->withErrors(['error' => 'Item Already Rejected']);
      }
      
      if (!$request->has('redo_reason') || strlen($request->get('redo_reason')) < 4) {
        return redirect()->back()->withErrors(['error' => 'Redo Reason is Required']);
      }
      
      if($item->batch_number != '0') {
        $result = $this->itemReject($item, $request->get('redo_quantity'), 7, $request->get('reason_to_reject'), 
                                                $request->get('redo_reason'), 'Redo', 0);
      } else {
        $item->item_status = 1;
        $item->tracking_number = null;
        $item->save();
      }
      
      $order = $item->order;
      
      $order->order_status = 4;
      $order->save();
      
      Order::note("Order status changed from Shipped to To Be Processed - Item $item->id redo after shipping", 
                    $order->order_5p, $order->order_id);
      
      $shipment = Ship::with('items')
                  ->where('order_number', $order->id)
                  ->where('tracking_number', $item->tracking_number)
                  ->first();
      
      if ($shipment && $shipment->items && count($shipment->items) == 0) {
        $shipment->delete();
      }
      
      return redirect()->action('OrderController@details', ['id' => $item->order_5p]);
    }
    
    public function itemReject ($item, $qty, $graphic_status, $reason, $message, $title, $scrap) {
      
      if ($graphic_status == 7) {
        $prefix = 'X';
      } else {
        $prefix = 'R';
      }
      
      $batch_number = $item->batch_number;
      
      if ($batch_number == '0') {
        return ['new_batch_number' => '0', 'reject_id' => $item->id, 'label' => null];
      }
      
      $original_batch_number = Batch::getOriginalNumber($batch_number);
      
      $reject_batch = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
                      ->join('rejections', 'items.id', '=', 'rejections.item_id')
                      ->select('batches.batch_number')
                      ->where('batches.status', 3)
                      ->where('items.item_status', 3)
                      ->where('rejections.graphic_status', $graphic_status)
                      ->where('batches.batch_number', 'LIKE', $prefix . '%' . $original_batch_number)
                      ->get();
      
      $old_batch = Batch::where('batch_number', $batch_number)
                    ->first();
      
      if (count($reject_batch) > 0) {
        
        $new_batch_number = $reject_batch->first()->batch_number;
        
      } else if ($old_batch) {
        
        $reject_batch = new Batch;
        $reject_batch->batch_number = Batch::getNewNumber($batch_number, $prefix);
        $reject_batch->save();
        $new_batch_number = $reject_batch->batch_number;
        $reject_batch->section_id = $old_batch->section_id;
        $reject_batch->station_id = $old_batch->station_id;
        $reject_batch->batch_route_id = $old_batch->batch_route_id;
        $reject_batch->production_station_id = $old_batch->production_station_id;
        $reject_batch->store_id = $old_batch->store_id;
        $reject_batch->creation_date = date("Y-m-d H:i:s");
        $reject_batch->change_date = date("Y-m-d H:i:s");
        $reject_batch->status = 'held';
        $reject_batch->save();

      } 
      
      if (abs($item->item_quantity - $qty) < 1) {
        
          $item->item_status = 'rejected';
          $item->batch_number = $new_batch_number;
          $item->tracking_number = null;
          $item->save();
          
          $reject_id = $item->id;
        
      } else {
          Log::info('Different reject quantity: ' . $item->id . ' - reject qty: ' . $qty);
          
          $update_qty = $item->item_quantity - $qty;
          
          $reject_item = new Item;
          $reject_item->batch_number = $item->batch_number;
          $reject_item->order_5p = $item->order_5p;
          $reject_item->order_id = $item->order_id;
          $reject_item->store_id = $item->store_id;
          $reject_item->item_code = $item->item_code;
          $reject_item->child_sku = $item->child_sku;
          $reject_item->item_description = $item->item_description;
          $reject_item->item_id = $item->item_id;
          $reject_item->item_option = $item->item_option;
          $reject_item->item_thumb = $item->item_thumb;
          $reject_item->item_unit_price = $item->item_unit_price;
          $reject_item->item_url = $item->item_url;
          $reject_item->data_parse_type = 'reject';
          $reject_item->sure3d = $item->sure3d;
          $reject_item->edi_id = $item->edi_id;
          $reject_item->item_quantity = $qty;
          $reject_item->tracking_number = null;
          $reject_item->item_status = 'rejected';
          $reject_item->save();
          
          $reject_item->batch_number = $new_batch_number;
          $reject_item->save();
          
          $item->item_quantity = $update_qty;
          $item->save();
          
          $reject_id = $reject_item->id;
      }
      
      $rejection = new Rejection;
      $rejection->item_id =$reject_id;
      $rejection->scrap = $scrap;
      $rejection->graphic_status = $graphic_status;
      $rejection->rejection_reason = $reason;
      $rejection->rejection_message = $message;
      $rejection->reject_qty = $qty;
      $rejection->rejection_user_id = auth()->user()->id;
      $rejection->from_station_id =  $old_batch->station_id;
      $rejection->from_batch =  $old_batch->batch_number; 
      $rejection->to_batch =  $new_batch_number;
      $rejection->from_screen =  $title;
      $rejection->save();
      
      $label = null;
      
      if ($scrap == '1') {
        foreach ($item->inventoryunit as $stock_no) {
          InventoryAdjustment::adjustInventory(8, $stock_no->stock_no_unique, $rejection->reject_qty * $stock_no->unit_qty, $rejection->id, $rejection->item_id);
        }
        
        $label = $this->getLabel($rejection);
        
      }
      
      // Order::note("Item $reject_id rejected: " . $rejection->rejection_reason_info->rejection_message, $item->order_5p);
      
      Wap::removeItem($reject_id, $item->order_5p);

      Batch::isFinished($old_batch->batch_number);
      
      return ['new_batch_number' => $new_batch_number, 'reject_id' => $reject_id, 'label' => $label];
    }
    
    public function rejectBatch (Request $request) {     
      
      $old_batch = Batch::with('items.inventoryunit')
                  ->where('id', $request->get('batch_id'))
                  ->first();
      
      if (!$old_batch) {
        return redirect()->back()->withErrors(['error' => 'Batch Not Found']);
      }
      
      $original_batch_number = Batch::getOriginalNumber($old_batch->batch_number);
      
      $rules = [
        'graphic_status'    => 'required',
        'rejection_reason'  => 'required|exists:rejection_reasons,id',
      ];
      
      $validation = Validator::make($request->all(), $rules);
      
      if ( $validation->fails() ) {
        return redirect()->back()->withErrors($validation);
      }
      
      $reject_batch = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
                      ->join('rejections', 'items.id', '=', 'rejections.item_id')
                      ->select('batches.batch_number')
                      ->where('batches.status', 3)
                      ->where('items.item_status', 3)
                      ->where('rejections.graphic_status', '=', $request->get('graphic_status'))
                      ->where('batches.batch_number', 'LIKE', 'R%' . $original_batch_number)
                      ->get();
      
      if (count($reject_batch) > 0) {
        
        $new_batch_number = $reject_batch->first()->batch_number;
        
      } else {
        $new_batch_number = Batch::getNewNumber($old_batch->batch_number, 'R');
        
        $reject_batch = new Batch;
        $reject_batch->batch_number = $new_batch_number;
        $reject_batch->section_id = $old_batch->section_id;
        $reject_batch->batch_route_id = $old_batch->batch_route_id;
        $reject_batch->production_station_id = $old_batch->production_station_id;
        $reject_batch->station_id = $old_batch->station_id;
        $reject_batch->store_id = $old_batch->store_id;
        $reject_batch->creation_date = date("Y-m-d H:i:s");
        $reject_batch->change_date = date("Y-m-d H:i:s");
        $reject_batch->status = 'held';
        $reject_batch->save();
      
      }
      
      foreach ($old_batch->items as $item) {
        
          $item->item_status = 'rejected';
          $item->batch_number = $new_batch_number;
          $item->vendor = null;
          $item->save();
          
          $rejection = new Rejection;
          $rejection->item_id = $item->id;
          $rejection->scrap = $request->get('scrap');
          $rejection->graphic_status = $request->get('graphic_status');
          $rejection->rejection_reason = $request->get('rejection_reason');
          $rejection->rejection_message = $request->get('rejection_message');
          $rejection->reject_qty = $item->item_quantity;
          $rejection->rejection_user_id = auth()->user()->id;
          $rejection->from_station_id =  $old_batch->station_id;
          $rejection->from_batch =  $old_batch->batch_number; 
          $rejection->to_batch =  $new_batch_number;
          $rejection->from_screen =  $request->get('title');
          $rejection->save();
          
          if ($request->get('scrap') == '1') {
            foreach ($item->inventoryunit as $stock_no) {
              InventoryAdjustment::adjustInventory(9, $stock_no->stock_no_unique, $rejection->reject_qty * $stock_no->unit_qty, $rejection->id, $rejection->item_id);
            }
          }
            
          Order::note("Item $item->id rejected: " . $rejection->rejection_reason_info->rejection_message, $item->order_5p, $item->order_id);
          
          Wap::removeItem($item->id, $item->order_5p);
      } 
      
      //send first reprint to production
      if ($request->get('graphic_status') == '1') {
        
        $item_ids = Item::where('batch_number', $new_batch_number)
                          ->where('is_deleted', '0')
                          ->select('id')
                          ->get()
                          ->pluck('id')
                          ->toArray();
                          
        $rejectcount = Rejection::whereIn('item_id', $item_ids)->count();
        
        if ($rejectcount == count($item_ids)) {
          $this->moveStation($new_batch_number);
          
          $msg = Batch::export($new_batch_number, '0');
          
          if (isset($msg['error'])) {
            Batch::note( $new_batch_number, '', 0, $msg['error']);
          }
        }
      }
      
      Batch::note( $old_batch->batch_number, '', 0, "Entire Batch Rejected and moved to " . $new_batch_number);
      Batch::note( $new_batch_number, '', 0, "Entire Batch Rejected from " . $old_batch->batch_number);
      
      Batch::isFinished($old_batch->batch_number);
      
      return redirect()->action('BatchController@show', ['batch_number' => $new_batch_number]);
    }
    
    public function reprintLabel (Request $request) {
      
      $rejection = Rejection::with('item.order', 'rejection_reason_info', 'user', 
                            'from_batch_info.scans.station', 'from_batch_info.scans.in_user', 'from_batch_info.scans.out_user') 
                            ->where('id', $request->get('id'))
                            ->first();
                            
      $label = $this->getLabel($rejection);
      
      return redirect()->action('RejectionController@index', ['label' => $label]);
    }
    
    
    public function getLabel($rejection) {

      $item = $rejection->item;
      $order_id = $rejection->item->order->short_order;
      
      if ($rejection->rejection_reason_info) {
        $reason = $rejection->rejection_reason_info->rejection_message;
      } else {
        $reason = '';
      }
      
      $username = $rejection->user->username;
      
      $last_scan = null;
      
      foreach ($rejection->from_batch_info->scans as $scan) {
        if($scan->station->type == "P") { 
          if (isset($scan->in_user)) {
            $last_scan = $scan->station->station_name . ' : IN ' . $scan->in_user->username . ' ' . substr($scan->in_date, 0, 10);
          }
           
          if (isset($scan->out_user)) {
            $last_scan .= ' - OUT ' . $scan->out_user->username . ' ' . substr($scan->in_date, 0, 10);
          }
          break;
        }
      }
      
      
      $label = "^XA" .
                "^FX^CF0,200^FO100,50^FDREJECT^FS^FO50,220^GB700,1,3^FS" .
                "^FX^CF0,30^FO50,240^FDItem ID: $rejection->item_id^FS^FO350,240^FDOrder ID:$order_id^FS^FO50,280^FDBatch: $rejection->to_batch^FS" .
                  "^FO50,320^FDDate: $rejection->created_at^FS^FO50,370^GB700,1,3^FS^FO50,400^FB750,3,,^FD$item->item_description^FS" .
                  "^FO50,440^FB750,2,,^FDSKU:$item->child_sku^FS" . 
                  "^FO100,480^FB560,6,,^FD" . Helper::optionTransformer($item->item_option, 1, 0, 0, 1, 0, ',  ') . "^FS" .
                "^FX^FO50,630^GB700,270,3^FS^CF0,40^FO75,650^FDRejected QTY: $rejection->reject_qty^FS" .
                "^FO350,650^FDRejected By: $username^FS^CF0,50^FO75,700^FDReason: $reason^FS" .
                  "^FO75,750^FB700,3,,^FD$rejection->rejection_message^FS" .
                  "^CF0,20^FO60,875^FDSCAN : $last_scan^FS" .
                "^FX^BY5,2,150^FO50,920^BC^FDITEM$rejection->item_id^FS" .
                "^XZ";
      
      return str_replace("'", " ", $label);
      
    }
    
    public function splitBatch (Request $request) {
      
      $item = Item::with('rejection')->find($request->get('item_id'));
      
      $old_batch = Batch::where('batch_number', $request->get('batch_number'))->first();
      
      $new_batch_number = Batch::getNewNumber($request->get('batch_number'), 'R');
      
      $reject_batch = new Batch;
      $reject_batch->batch_number = $new_batch_number;
      $reject_batch->section_id = $old_batch->section_id;
      $reject_batch->batch_route_id = $old_batch->batch_route_id;
      $reject_batch->production_station_id = $old_batch->production_station_id;
      $reject_batch->station_id = $old_batch->station_id;
      $reject_batch->store_id = $old_batch->store_id;
      $reject_batch->creation_date = date("Y-m-d H:i:s");
      $reject_batch->change_date = date("Y-m-d H:i:s");
      $reject_batch->status = 'held';
      $reject_batch->save();
      
      $item->batch_number = $new_batch_number;
      $item->save();
      
      $item->rejection->to_batch = $new_batch_number;
      $item->rejection->save();
      
      Batch::note( $request->get('batch_number'), '', 0, "Item $item->id split into reject batch $new_batch_number");
      Batch::note($new_batch_number, '', 0, "Item $item->id split from reject batch $old_batch->batch_number");
      
      return redirect()->action('RejectionController@index');
    }
    
}
