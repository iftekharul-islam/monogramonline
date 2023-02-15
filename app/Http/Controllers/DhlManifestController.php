<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Item;
use App\Order;
use App\DhlManifest;
use App\Ship;
use App\Store;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Monogram\Ship\DHL;
use Ship\Shipper;


class DhlManifestController extends Controller
{
    public static $search_in = [
        'mail_class' => 'Shipped Via',
        'manifestId' => 'Manifest ID',
        'user' => 'Create By',
    ];


    public function index(Request $request)
    {
        if (!$request->has('search_for_first') && !$request->has('search_for_second')
            && !$request->has('start_date') && !$request->has('end_date')) {

            $start_date = date("Y-m-d");
        } else {
            $start_date = $request->get('start_date');
        }

        $dhlManifestList = DhlManifest::with('user')
            ->where('is_deleted', 0)
            ->searchCriteria($request->get('search_for_first'), $request->get('search_in_first'))
            ->searchCriteria($request->get('search_for_second'), $request->get('search_in_second'))
            ->searchStoreId($request->get('store_id'))
            ->searchWithinDate($start_date, $request->get('end_date'))
            ->latest('created_at')
            ->paginate(10);

        $totalMainFeast = Ship::whereNotNull('tracking_number')
            ->where('is_deleted', 0)
            ->searchWithinDate($start_date, $request->get('end_date'))
            ->where('tracking_type',"DHL")
            ->count();

        $noMainFeast = Ship::whereNotNull('tracking_number')
            ->where('is_deleted', 0)
            ->searchWithinDate($start_date, $request->get('end_date'))
            ->where('tracking_type',"DHL")
            ->where('manifestStatus',1)
            ->count();
//dd($totalMainFeast, $noMainFeast);

        $stores = Store::list();
        $userlist = User::userlist();
        $totalMag = "Total label ". $totalMainFeast ." - Total Mainfest created  ". $noMainFeast." = ".($totalMainFeast- $noMainFeast). " left";
        return view('dhl_manifest.index', compact('dhlManifestList','request','stores','userlist','totalMag'))
            ->with('search_in', static::$search_in);
    }

    public function getDhlManifest(Request $request)
    {
//        dd("getDhlManifest", $request->all(), $request->get("dhlInternationalManifest_date"));
//        $dhlManifestDate = $request->get("dhlManifest_date");
        $dhl = new DHL();
        $result = $dhl->getDhlManifest($request->get("dhlManifest_date"));
        if($result == "success"){
            return redirect()->action('DhlManifestController@index')->withSuccess('DhlManifest saved successfully.');
        }else{
            return redirect()->action('DhlManifestController@index')->withErrors($result);
        }
    }

    public function getDhlInternationalManifest(Request $request)
    {
//        dd("getDhlInternationalManifest", $request->all(), $request->get("dhlInternationalManifest_date"));
//        $dhlManifestDate = $request->get("dhlManifest_date");
        $dhl = new DHL();
        $result = $dhl->getDhlInternationalManifest($request->get("dhlInternationalManifest_date"));

        if($result == "success"){
            return redirect()->action('DhlManifestController@index')->withSuccess('DhlManifest saved successfully.');
        }else{
            return redirect()->action('DhlManifestController@index')->withErrors($result);
        }

    }

}
