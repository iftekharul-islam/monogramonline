<?php

namespace App;

use App\Http\Controllers\DhlManifestController;
use Monogram\Taskable;

// use Illuminate\Database\Eloquent\Model;

class DhlManifest extends Taskable
{
    protected $table = 'dhl_manifest';

    public static function getTableColumns()
    {
        return (new static())->tableColumns();
    }

    private function tableColumns()
    {
        $columns = $this->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($this->getTable());

        return array_slice($columns, 1, -2);
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user', 'id');

    }

    public function scopeSearchCriteria($query, $search_for, $search_in)
    {
        $search_for = trim($search_for);
        if (in_array($search_in, array_keys(DhlManifestController::$search_in))) {
            /*
             * camel case method converts the key to camel case
             * uc first converts the word to upper case first to match the method name
             */
            $search_function_to_respond = sprintf("scopeSearch%s", ucfirst(camel_case($search_in)));

            return $this->$search_function_to_respond($query, $search_for);
        }

        return;
    }


    public function scopeSearchUniqueOrderId($query, $packageId)
    {
        if (empty($packageId)) {
            return;
        }

        if (strpos($packageId, '-') == false) {
            $packageId = substr_replace($packageId, '%', -1, 0);
        }

        return $query->where('unique_order_id', "LIKE", sprintf("%%%s%%", $packageId));
    }

    public function scopeSearchUser($query, $username)
    {
        if (empty($username)) {
            return;
        }

        return $query->whereHas('user', function ($q) use ($username) {
            return $q->where('username', 'LIKE', sprintf("%%%s%%", $username));
        });
    }


    public function scopeSearchStoreId($query, $store_id)
    {
        if (empty($store_id)) {
            return;
        }

        return $query->where('store_id', intval($store_id));
    }


    public function scopeSearchManifestId($query, $manifestId)
    {
        if (empty($manifestId)) {
            return;
        }
        return $query->where('manifestId', intval($manifestId));

    }


    public function scopeSearchMailClass($query, $mail_class)
    {
        if (empty($mail_class)) {
            return;
        }

        return $query->where('mail_class', "LIKE", sprintf("%%%s%%", $mail_class));
    }

    public function scopeSearchWithinDate($query, $start_date, $end_date)
    {
        if (!$start_date) {
            return;
        }

        if (!$end_date) {
            $end_date = date("Y-m-d");
        }
        // formatting the date again, if, malformed, won't crash
        $start_date = date('Y-m-d', strtotime($start_date));
        if ($end_date) {
            $end_date = date('Y-m-d', strtotime($end_date));
        } else {
            $end_date = $start_date;
        }
        $starting = $start_date . " 00:00:00";
        $ending = $end_date . " 23:59:59";
// 		dd($starting, $ending);
        // postmark_date transaction_datetime
        return $query->where('created_at', '>=', $starting)
            ->where('created_at', '<=', $ending);
    }

    public function outputArray()
    {
        return ['App\DhlManifest',
            $this->id,
            url(sprintf('shippingMainfest?search_for_first=%s&search_in_first=unique_order_id', $this->manifestId)),
        ];
    }
}
