<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	use Authenticatable, CanResetPassword, Authorizable {
		
	}

	/**
	 * The database table used by the model.
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 * @var array
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

    protected $casts = [
        'permit_manufactures' => 'array'
    ];

	public function setPasswordAttribute ($value)
	{
		$this->attributes['password'] = bcrypt($value);
	}

	public function accesses ()
	{
		return $this->hasMany('App\Access', 'user_id', 'id');
	}

    public static function userlist ()
    {
        $array =  User::where('is_deleted', '0')
            ->orderBy('id')
            ->get()
            ->pluck('username','id')
            ->toArray();
        return $array;
    }

}
