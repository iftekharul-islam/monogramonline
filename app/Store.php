<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Store extends Model
{
    protected $casts = ['permit_users'=> 'array'];

    protected function castAttribute($key, $value)
    {
        if ($this->getCastType($key) == 'array' && is_null($value)) {
            return [];
        }

        return parent::castAttribute($key, $value);
    }

	public static $companies = ['null' => '','0' => 'Monogramonline', '1' => 'Natico', '2' => 'PWS', '3' => 'Dropship'];
	
	public function store_items () {
		return $this->hasMany('App\StoreItem', 'store_id', 'store_id')
										->where('is_deleted', '0');
	}
	
	public static function list ($batch = '%', $company = '%', $prepend = ['',''])
	{
		$array =  Store::where('is_deleted', '0')
								->where('batch', 'LIKE', $batch)
								->where('company', 'LIKE', $company)
								->where('invisible', '0')
								->orderBy('sort_order')
                                ->where('permit_users', 'like', "%".auth()->user()->id ."%")
								->get()
								->pluck('store_name', 'store_id');
		
		if (isset($prepend) && $prepend != 'none') {
								$array->prepend($prepend[0], $prepend[1]);
		} 
		
		return $array;
	}
	
	public function scopeSearch ($query, $store_id)
	{
		if ( !$store_id ) {
			return;
		}

		return $query->where('store_id', $store_id);
	}
		
	public static function notifyOptions() {
		return [
						'0' => 'None',  
						'1' => 'API / FTP (EDI Class)', 
						'2' => 'E-mail to Customer', 
						'3' => 'E-mail and API',
						'4' => 'Export File'
					];
	}
	
	public static function inputOptions() {
		return [
						'0' => 'None', 
						'1' => 'API / FTP (EDI Class)', 
						'2' => 'WebHook',
						'3' => 'File Import'
					];
	}
	
	public static function batchOptions() {
		return [
						'0' => 'Together with other stores', 
						'1' => 'Separately',
						'2' => 'Separately at Import'
					];
	}
	
	public static function qcOptions() {
		return [
						'0' => 'Normal QC', 
						'1' => 'QC by Shipping Admin'
					];
	}
	
	public function loadClass ($class_name) {
		return call_user_func(array($class_name, "get_instance"));
	}
	
}
