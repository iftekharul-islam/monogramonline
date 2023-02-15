<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Store;
use App\StoreItem;
use Monogram\CSV;

class StoreItemController extends Controller
{
    
    protected $columns = [  'store_id',
                            'vendor_sku',
                            'description',
                            'cost',
                            'parent_sku',
                            'child_sku',
                            'url',
                            'upc',
                            'custom'
                          ];
                          
    public function index($store_id)
    { 
        $store = Store::where('store_id', $store_id)->first();
        
        if (!$store) {
          return redirect()->back()->withErrors('Store ' . $store_id . ' not found');
        }
        
        $items = StoreItem::searchStore($store_id)
                            ->where('is_deleted', '0')
                            ->orderBy('vendor_sku')
                            ->get();
        
        return view('store_items.index', compact('store', 'items'));
        
    }
    
    public function getCSV($store_id) {
      
      $items = StoreItem::searchStore($store_id)
                          ->where('is_deleted', '0')
                          ->orderBy('vendor_sku')
                          ->get($this->columns)
                          ->toArray();
      
      $filename = sprintf("%s_items_%s.csv", $store_id, date("Y_m_d_His", strtotime('now')));

      $csv = new CSV;
      $pathToFile = $csv->createFile($items, 'assets/exports/', $this->columns, $filename);
      
      return response()->download($pathToFile)->deleteFileAfterSend(true);
      
    }
    
    public function uploadCSV(Request $request) {
      
      $store_id = $request->get('store_id');
      
      $file = $request->file('items_csv');
      
      if ( !$file || $file->getClientMimeType() != 'text/csv' ) {
        return redirect()->back()->withErrors('Missing or Invalid File.');
      }
      
      $file_path =  public_path() . '/assets/imports/';
      $file_name = $file->getClientOriginalName();
      
      $file->move($file_path, $file_name);
      
      $csv = new CSV;
      $items = $csv->intoArray($file_path . $file_name, ",");
      
      $errors = array();
      
      foreach ($items as $index => $row) {
        
        if ($index == 0 && $row != $this->columns) {
          return redirect()->back()->withErrors('Incorrect CSV columns.');
        } elseif ($index == 0) {
          continue;
        }
        
        if ($index == 1 && $row[0] != $store_id) {
          return redirect()->back()->withErrors('Incorrect Store ID in CSV.');
        } elseif ($index == 1) {
          StoreItem::where('store_id', $store_id)->delete();
        }
        
        try {
          
          $store_item = new StoreItem;
          $store_item->store_id = $store_id;
          $store_item->vendor_sku = trim($row[1]);
          $store_item->description = trim($row[2]);
          $store_item->cost = trim(str_replace('$', '', $row[3]));
          $store_item->parent_sku = trim($row[4]);
          $store_item->child_sku = trim($row[5]);
          $store_item->url = trim($row[6]);
          $store_item->upc = trim($row[7]);
          $store_item->custom = trim($row[8]);
          
          $store_item->save();
          
        } catch (\Exception $e) {
          $errors[] = 'Error entering item information : ' . $row[1];
        }
      }
      
      return redirect()->action('StoreItemController@index', ['store_id' => $store_id])->withErrors($errors);
    }
    
    public function store(Request $request)
    {
        
        if (!$request->has('store_id')) {
          return redirect()->back()->withErrors('Store not set!');
        }
        
        if (!$request->has('vendor_sku') || !$request->has('parent_sku')) {
          return redirect()->back()->withErrors('Vendor SKU and Parent SKU are required');
        }
        
        $item = new StoreItem;
        $item->store_id = $request->get('store_id');
        $item->custom = $request->get('custom');
        $item->vendor_sku = $request->get('vendor_sku');
        $item->description = $request->get('description');
        $item->cost = $request->get('cost');
        $item->parent_sku = $request->get('parent_sku');
        $item->child_sku = $request->get('child_sku');
        $item->url = $request->get('url');
        $item->upc = $request->get('upc');
        $item->save();
        
        return redirect()->action('StoreItemController@index', ['store_id' => $request->get('store_id')])
                          ->withSuccess('Item Added');
    }

    public function update(Request $request)
    {
      
      if (!$request->has('store_id')) {
        return redirect()->back()->withInput()->withErrors('Store not set!');
      }
      
      if (!$request->has('vendor_sku') || !$request->has('parent_sku')) {
        return redirect()->back()->withInput()->withErrors('Vendor SKU and Parent SKU are required');
      }
      
      $item = StoreItem::find($request->get('id'));
      
      if (!$item) {
        return 'failed';
      }
      
      $item->store_id = $request->get('store_id');
      $item->custom = $request->get('custom');
      $item->vendor_sku = $request->get('vendor_sku');
      $item->description = $request->get('description');
      $item->cost = $request->get('cost');
      $item->parent_sku = $request->get('parent_sku');
      $item->child_sku = $request->get('child_sku');
      $item->url = $request->get('url');
      $item->upc = $request->get('upc');
      $item->save();
      
      return 'updated';
    }

    public function destroy($id)
    {
      $item = StoreItem::find($id);
      
      if (!$item) {
        return 'failed';
      }
      
      $item->is_deleted = '1';
      $item->save();
      
      return redirect()->action('StoreItemController@index', ['store_id' => $item->store_id])
                        ->withSuccess('Item Deleted');
    }
}
