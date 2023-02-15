<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class WaDashboardController extends Controller
{

//get('getwasatchstatus', 'WaDashboardController@index');       http://order.monogramonline.com/getwasatchstatus
//get('getwasatch11status', 'WaDashboardController@get11Pc');   http://order.monogramonline.com/getwasatch11status
//get('getwasatch23status', 'WaDashboardController@get23Pc');   http://order.monogramonline.com/getwasatch23status
//get('getwasatch130status', 'WaDashboardController@get130Pc'); http://order.monogramonline.com/getwasatch130status

    public function index()
    {
        $queue = array();

        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=1";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=2";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=3";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=4";
	$dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=5";
	$dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=6";



//        $dashboard_ip[] = "http://10.10.0.23/xmlQueueStatus.dyn?PRINTUNIT=1"; //6
//        $dashboard_ip[] = "http://10.10.0.23/xmlQueueStatus.dyn?PRINTUNIT=2"; //5

//        $dashboard_ip[] = "http://10.10.0.130/xmlQueueStatus.dyn?PRINTUNIT=1"; //1
//        $dashboard_ip[] = "http://10.10.0.130/xmlQueueStatus.dyn?PRINTUNIT=2"; //2


//        for($i = 1; $i<7; $i++){
        foreach ($dashboard_ip as $url){
            $input = $this->simpleRequest($url);

            if ($input === false) {
                Log::error('Wasatch: Cannot retrieve Queue Info');
                return 'Error: Cannot retrieve Wasatch queues';
            }

            array_push($queue,$input);
        }
//        return $queue;
        return response()->json($queue, 200);
    }


    public function get11Pc()
    {
        $queue = array();

        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=1";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=2";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=3";
        $dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=4";
	$dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=5";
	$dashboard_ip[] = "http://10.10.0.11/xmlQueueStatus.dyn?PRINTUNIT=6";


        foreach ($dashboard_ip as $url){
            $input = $this->simpleRequest($url);
            if ($input === false) {
                Log::error('Wasatch: Cannot retrieve Queue Info');
                return 'Error: Cannot retrieve Wasatch queues';
            }
            array_push($queue,$input);
        }
        return response()->json($queue, 200);
    }

    public function get23Pc()
    {
        $queue = array();
        $dashboard_ip[] = "http://10.10.0.23/xmlQueueStatus.dyn?PRINTUNIT=1";
        $dashboard_ip[] = "http://10.10.0.23/xmlQueueStatus.dyn?PRINTUNIT=2";

        foreach ($dashboard_ip as $url){
            $input = $this->simpleRequest($url);
            if ($input === false) {
                Log::error('Wasatch: Cannot retrieve Queue Info');
                return 'Error: Cannot retrieve Wasatch queues';
            }
            array_push($queue,$input);
        }
        return response()->json($queue, 200);
    }

    public function get130Pc()
    {
        $queue = array();
        $dashboard_ip[] = "http://10.10.0.130/xmlQueueStatus.dyn?PRINTUNIT=1";
        $dashboard_ip[] = "http://10.10.0.130/xmlQueueStatus.dyn?PRINTUNIT=2";

        foreach ($dashboard_ip as $url){
            $input = $this->simpleRequest($url);
            if ($input === false) {
                Log::error('Wasatch: Cannot retrieve Queue Info');
                return 'Error: Cannot retrieve Wasatch queues';
            }
            array_push($queue,$input);
        }
        return response()->json($queue, 200);
    }



    private function simpleRequest($url) {

        try {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml=curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            Log::info('Wasatch Curl Error: ' . $e->getMessage());
            return false;
        }

        if($xml != 404) {
            try {
                $doc = simplexml_load_string($xml);
                $json = json_encode($doc);
//          Log::info('XXXEEEE Curl result: ' . json_decode($json,TRUE));
                return json_decode($json,TRUE);
            } catch (\Exception $e) {
                Log::info('Wasatch XML Decode Error: ' . $e->getMessage());
                return false;
            }
        } else {
            Log::info('Wasatch 404 Error');
            return false;
        }
    }

}

