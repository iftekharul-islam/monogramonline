<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class CustomValidationProvider extends ServiceProvider
{
	private function class_checker ($class)
	{
		return class_exists($class);
	}

	/**
	 * Bootstrap the application services.
	 * @return void
	 */
	public function boot ()
	{
		Validator::extend('no_space_allowed', function ($attribute, $value, $parameters, $validator) {
			return count(explode(' ', $value)) == 1;
		});
		Validator::replacer('no_space_allowed', function ($message, $attribute, $rule, $parameters) {
			return $message;
		});

		// on updates
		// if a value is passed check for the uniqueness for the value in that table
		// parameters are, ModelName, Id, Field to check
		Validator::extend('uniqueness_in_model', function ($attribute, $value, $parameters, $validator) {
			$model = null;
			$id = null;
			$field = null;
			// get the model, id and field (to get the value) from parameters
			list( $model, $id, $field ) = $parameters;
			// check if any value is not present, return false
			if ( is_null($model) || is_null($id) || is_null($field) ) {
				return false;
			}
			try {
				// check if the model is present
				// if the fully specified model is not given
				// append App namespace prefix to the model
				$model = $this->class_checker($model) ? $model : sprintf("App\\%s", $model);

				// check again if it's good or not
				if ( !$this->class_checker($model) ) {
					return false;
				}

				$record = $model::where($field, $value)
								->first();
				// if the record is not available, then can update
				// or if the id of the model record is same as the found record, then can update
				return !$record || $record->id == $id ? true : false;
			} catch ( \Exception $ex ) {
				return false;
			}

		});
		Validator::replacer('uniqueness_in_model', function ($message, $attribute, $rule, $parameters) {
			return $message;
		});


	}

	/**
	 * Register the application services.
	 * @return void
	 */
	public function register ()
	{
		//
	}
}
