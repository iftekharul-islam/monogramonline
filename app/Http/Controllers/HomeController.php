<?php

namespace App\Http\Controllers;

use App\Access;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Order;
use App\Ship;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class HomeController extends Controller
{

    public function index()
    {
        $access = Access::$pages;
        $user_access = auth()
            ->user()
            ->accesses()
            ->get()
            ->pluck('page')->toArray();

        return view('home.index', compact('access', 'user_access'));
    }

    public function index2()
    {

        if(request()->has("old")) {
            return view('home.index');
        }
        $orders = Order::query()->where("created_at", ">=", Carbon::now()->subDays(30)->toDateString())->count();
        $orders2 = Order::query()->where("created_at", ">=", Carbon::now()->startOfDay()->toDateString())->count();

        $directory = "/media/RDrive/archive/";
        $filecount = 0;
        $files = glob($directory . "*");
        if ($files) {
            $filecount = count($files);
        }


        $archive = $filecount;
        $archiveSize = Cache::remember('users', (60 * 60) * 2, function () use ($directory) {
            return $this->format_size($this->dir_size($directory));
        });



        $recentOrders = Order::orderBy('id', 'desc')
            ->with("items")
            ->where("created_at", ">=", Carbon::now()->subHours(5)->toDateString())
            ->take(5)->get();


        $recentOrders2 = Ship::with("items")
            ->where("created_at", ">=", Carbon::now()->subHours(24)->toDateString())
            ->take(5)->get();


        return view('home.index2', compact('stations', 'orders', 'orders2', 'archive', 'recentOrders', 'recentOrders2', 'archiveSize'));
    }

    public function dir_size($directory)
    {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    public function format_size($size)
    {
        $mod = 1024;
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}