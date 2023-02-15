<?php namespace Monogram;

use App\BatchRoute;
use App\Inventory;
use App\MasterCategory;
use App\Option;
use App\Parameter;
use App\Product;
use App\StoreItem;
use DNS1D;
use Illuminate\Support\Facades\Log;

class Helper
{
	private static $state_abbrev = array(
								'alabama' => 'AL',
								'alaska' => 'AK',
								'arizona' => 'AZ',
								'arkansas' => 'AR',
								'california' => 'CA',
								'colorado' => 'CO',
								'connecticut' => 'CT',
								'delaware' => 'DE',
								'florida' => 'FL',
								'georgia' => 'GA',
								'hawaii' => 'HI',
								'idaho' => 'ID',
								'illinois' => 'IL',
								'indiana' => 'IN',
								'iowa' => 'IA',
								'kansas' => 'KS',
								'kentucky' => 'KY',
								'louisiana' => 'LA',
								'maine' => 'ME',
								'maryland' => 'MD',
								'massachusetts' => 'MA',
								'michigan' => 'MI',
								'minnesota' => 'MN',
								'mississippi' => 'MS',
								'missouri' => 'MO',
								'montana' => 'MT',
								'nebraska' => 'NE',
								'nevada' => 'NV',
								'new hampshire' => 'NH',
								'new jersey' => 'NJ',
								'new mexico' => 'NM',
								'new york' => 'NY',
								'north carolina' => 'NC',
								'north dakota' => 'ND',
								'ohio' => 'OH',
								'oklahoma' => 'OK',
								'oregon' => 'OR',
								'pennsylvania' => 'PA',
								'rhode island' => 'RI',
								'south carolina' => 'SC',
								'south dakota' => 'SD',
								'tennessee' => 'TN',
								'texas' => 'TX',
								'utah' => 'UT',
								'vermont' => 'VT',
								'virginia' => 'VA',
								'washington' => 'WA',
								'west virginia' => 'WV',
								'wisconsin' => 'WI',
								'wyoming' => 'WY',
								'british columbia' => 'BC',
								'newfoundland and labrador' => 'NL',
								'prince edward island' => 'PE',
								'nova scotia' => 'NS',
								'new brunswick' => 'NB',
								'quebec' => 'QC',
								'ontario' => 'ON',
								'manitoba' => 'MB',
								'saskatchewan' => 'SK',
								'alberta' => 'AB',
								'yukon' => 'YT',
								'northwest territories' => 'NT',
								'nunavut' => 'NU',
								'district of columbia' => 'DC',
								'virgin islands' => 'VI',
								'guam' => 'GU',
						);
						
	public static $specSheetSampleDataArray = [
		'Yes'              => 'Yes',
		'No'               => 'No',
		'Redo Sample'      => 'Redo Sample',
		'Complete'         => 'Complete',
		'Sample Approve'   => 'Sample Approve',
		'Graphic Complete' => 'Graphic Complete',
	];

	// public static $webImageStatus = [
	// 	'Select web image status',
	// 	'Temporary',
	// 	'Create Web Image',
	// 	'Update Web Image',
	// 	'Web Image Approval',
	// 	'Publish Web image',
	// 	'Complete - Final Image Uploaded',
	// ];
	
	public static function stateAbbreviation ($state) 
	{
		if (isset(static::$state_abbrev[strtolower($state)])) { 
			 return static::$state_abbrev[strtolower($state)]; 
		 } else { 
			 return $state; 
		 } 
	}

	public static function scrollableCheckbox ($name, $options, $value = null)
	{
		$container = <<<Container
<div style="height: 12em; width: 20em; overflow: auto;">
				<div class="checkbox">
Container;
		foreach ( $options as $optionKey => $optionValue ) {
			$checked = '';
			if ( is_array($value) ) {
				$values = array_values($value);
				if ( in_array($optionKey, $values) ) {
					$checked = 'checked';
				}
			} elseif ( ! is_null($value) ) {
				if ( $optionKey == $value ) {
					$checked = 'checked';
				}
			}
			$input = <<<INPUT
					<label>
						<input type="checkbox" value="{$optionKey}" name="{$name}" {$checked}>
						{$optionValue}
					</label>
INPUT;
			$container .= $input;
		}
		$container .= <<<APPEND
				</div>
</div>
APPEND;

		return $container;
	}

	public static function getProductCount ($category_id)
	{
		#return Product::where('product_master_category', $category_id)->count();
		return Product::searchMasterCategory($category_id)->count();
	}

	public static function getHtmlBarcode ($value, $width = 1)
	{
		#return DNS1D::getBarcodeHTML($value, "C39", $width);
		return static::getImageBarcodeSource($value, $width);
	}

	public static function getImageBarcodeSource ($value, $width = 1)
	{
		return '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($value, "C39+", $width, 85) . '" alt="barcode"   />';
		#return '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+") . '" alt="barcode"   />';
	}

	public static function getCategoryHierarchy ($category_id, &$holder)
	{
		$category = MasterCategory::find($category_id);
		if ( $category ) {
			$holder->push($category);
			if ( $category->parent != 0 ) {
				self::getCategoryHierarchy($category->parent, $holder);
			}
		}
	}
	
	public static function optionTransformer ($json, $show_keys = 1, $html_bold = 0, $html_upsell = 0, 
																							$parameters = 1, $eps = 1, $separator = "\n")
	{	
		$pre = '';
		$post = '';
		$upsell_pre = '';
		$upsell_post = '';
		$delete_keys = array();
		
		if ($html_bold == 1) {
			$pre = '<strong style="font-size: 110%;">';
			$post = '</strong>';
		}
		
		if ($html_upsell == 1) {
			$upsell_pre = '<span style="font-size: 150%;color:red;">';
			$upsell_post = '</span>';
		}
		
		if ($parameters == 0) {
			
			$delete_keys = Parameter::selectRaw('REPLACE(LOWER(parameter_value)," ","_") as parameter') 
										->where('is_deleted', '0') 
										->get() 
										->pluck('parameter') 
										->toArray(); 
										
		} else {
			$delete_keys = array();
		}
		
		$delete_keys[] = 'confirmation_of_order_details';
		
		if ($eps == 0) {
			$delete_keys[] = 'custom_eps_download_link';
			$delete_keys[] = 'photo';
			$delete_keys[] = 'photo_2';
			$delete_keys[] = 'graphic';
		}
		
		$formatted_string = '';
		$array = json_decode($json, true);

		if ( $array ) { 
			foreach ($array as $key => $value) {
				$ckey = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','',trim(strtolower($key)));
				if (in_array($ckey, $delete_keys)) {
					unset($array[$key]);
				} else if (strtolower(str_replace([',', ' '], '', $value)) == 'nothankyou') {
					unset($array[$key]);
				} else if (strtolower(substr($value, 0, 3)) == 'yes') {
					if ($show_keys == 1) {
						$formatted_string .= str_replace("_", " ", $key) . ' = ';
					}
					$formatted_string .= sprintf("%s%s%s%s%s%s", $pre, $upsell_pre, $value, $upsell_post, $post, $separator);
				} else {
					if ($show_keys == 1) {
						$formatted_string .= str_replace("_", " ", $key) . ' = ';
					}
					$formatted_string .= sprintf("%s%s%s%s", $pre, $value, $post, $separator);
				}
			}
		}
		
		return $formatted_string ?: "";
	}
	
	public static function jsonTransformer ($json, $separator = "\n", $bold = 0)
	{
		if ($bold == 1) {
			$pre = '<strong style="font-size: 110%;">';
			$post = '</strong>';
		} else {
			$pre = '';
			$post = '';
		}
		
		$formatted_string = '';
		$json_array = json_decode($json, true);
		if ( $json_array ) {
			foreach ( $json_array as $key => $value ) {
				if ($key != 'Confirmation_of_Order_Details' && $key != 'couponcode') {
					if (strpos($value, '$') && $bold == 1) {
						$value = '<span style="font-size: 120%;">' . $value . '</span>';
					}
					$formatted_string .= sprintf("%s = %s%s%s%s", str_replace("_", " ", $key), $pre, $value, $post, $separator);
				}
			}
		}

		return $formatted_string ?: "";
	}


	public static function getDefaultRouteId ()
	{
		return 115;
	}

	public static function textToHTMLFormName ($text)
	{
		// double underscore is for protection
		// in case a single underscore found on string won't be replaced
		return str_replace(" ", "_", trim($text));
	}

	public static function htmlFormNameToText ($text)
	{
		return str_replace("_", " ", $text);
	}


	// public static function getItemCount ($items)
	// {
	// 	$total = 0;
	// 	foreach ( $items as $item ) {
	// 		$total += $item->item_quantity;
	// 	}
	// 
	// 	return $total;
	// }

	public static function getUniquenessRule ($model, $id, $field)
	{
		return sprintf("uniqueness_in_model:%s,%d,%s", $model, $id, $field);
	}
	
	public static function findProduct ($input, $store_id = null) {
		
		if ($store_id != null) {
			$store_item = StoreItem::where('store_id', $store_id)
																->where('vendor_sku', $input)
																->first();
			if ($store_item && $store_item->parent_sku != '') {
				$input = $store_item->parent_sku;
			}
		}
		
		$SKU = str_replace('_', '-', trim($input));
		
		$product = Product::where('product_model', 'LIKE', $SKU)->first();
		if ( ! $product ) {
			$SKU = substr($SKU, 0, strrpos($SKU, '-'));
			if ($SKU != '') {
				$product = Product::where('product_model', 'LIKE', $SKU)->first();
				if ( !$product ) {
					$SKU = substr($SKU, 0, strrpos($SKU, '-'));
					if ($SKU != '') {
						$product = Product::where('product_model', 'LIKE', $SKU)->first();
						if ( !$product ) {
							$SKU = substr($SKU, 0, strrpos($SKU, '-'));
							if ($SKU != '') {
								$product = Product::where('product_model', 'LIKE', $SKU)->first();
								if ( !$product ) {
									return false;
								}
							}
						} 
					} else {
							return false;
					}
				}
			} else {
					return false;
			}
		} 
		return $product;
	}
	
	public static function getChildSku ($item, $vendor_sku = null)
	{	
		$store_item = StoreItem::where('store_id', $item->store_id)
																->where('parent_sku', $item->item_code)
																->searchVendorSku($vendor_sku)
																->get();
																
		if ($store_item && count($store_item) == 1 && $store_item->first()->child_sku != '') {
			 return $store_item->first()->child_sku;
		} 
		
		// related to parameter options table
		// get the item options from order
		$item_options = json_decode($item->item_option, true);
		// Check is item_options an array
		if ( ! is_array($item_options) ) {
			return $item->item_code;
		}
		// get the keys from that order options
		$item_option_keys = array_map(function ($element) {
			return strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $element))); 
		}, array_keys($item_options));
		
		// $store_id = $item->store_id;
		
		// get the keys available as parameter
		$parameters = Parameter::where('is_deleted', '0')
								 ->get()
								 ->pluck('parameter_value')
								 ->toArray();
								 
		$parameter_to_html_form_name = array_map(function ($element) {
			return Helper::textToHTMLFormName(strtolower($element));
		}, $parameters);
		
		$parameter_options = Option::where('parent_sku', $item->item_code)
								   ->get(); 
									 
		// get the common in the keys
		$options_in_common = array_intersect($parameter_to_html_form_name, $item_option_keys);
		
		//generate the new sku
		$child_sku = static::generateChildSKU($options_in_common, $parameter_options, $item);
		
		return $child_sku;
	}

	private static function generateChildSKU ($matches, $parameter_options, $item)
	{
		// parameter options is an array of rows
		$item_options = json_decode($item->item_option, true);
		
		// 20160515 remove (+ character from child_sku
		$explode_values = [];
		foreach ( $item_options as $item_key => $item_value ) {
			$explode_values = explode("(", $item_value);
			if ( count($explode_values) > 0 ) {
				$item_options[ strtolower(trim($item_key)) ] = str_replace(['&quot;','&amp;'], '', $explode_values[0]);
			}
		}
		
		if (count($matches) > 0) {
			foreach ( $parameter_options as $option ) { 
				if ($option->parameter_option != null) {
					// item options has replaced space with underscore
					// parameter options has spaces intact
					$parameter_option_json_decoded = json_decode(strtolower($option->parameter_option), true); 
					$match_broken = false;
					foreach ( $matches as $match ) {
						// matches are underscored
						// i,e: form name
						// convert to text for parameter options
						// if ( $parameter_option_json_decoded[Helper::htmlFormNameToText($match)] != $item_options[$match] ) {
						if ( ! array_key_exists(Helper::htmlFormNameToText($match), $parameter_option_json_decoded) || ! array_key_exists($match, $item_options) || ( $parameter_option_json_decoded[ Helper::htmlFormNameToText($match) ] != $item_options[ $match ] ) ) {
							$match_broken = true;
							break;
						}
					}
					// if the inner loop
					// executes thoroughly
					// then the match_broken will be false always
					// break the outer loop
					// return the value
					// if the match is not broken.
					// if all the matches are found
					// will not
					if ( ! $match_broken ) {
						return $option->child_sku;
						//break;
					}
				}
			}
		}
		// child sku suggestion
		// no option was found matching
		// suggest a new child sku
		$child_sku_postfix = implode("-", array_map(function ($node) use ($item_options) {
			// replace the spaces with empty string
			// make the string lower
			// and the values from the item options
			return str_replace(" ", "", strtolower($item_options[ $node ]));
		}, $matches));

		$child_sku = empty($child_sku_postfix) ? $item->item_code : sprintf("%s-%s", $item->item_code, $child_sku_postfix);

		// Replace Please Select
		$child_sku = str_replace("-pleaseselect", "", $child_sku);
		
		// should have to match the previous check.
		// again check if the child sku is present or not
		return Helper::insertOption($child_sku, $item, $matches, $item_options);
		
	}
	
	public static function insertOption($child_sku, $item, $matches = null, $item_options = null) {
		
		$option = Option::where('child_sku', $child_sku)->first();
		
		if ( ! $option ) {
			$option = new Option();
			$option->child_sku = $child_sku;
			$option->unique_row_value = static::generateUniqueRowId();
			$option->id_catalog = $item->item_id;
			$option->parent_sku = $item->item_code;
			$option->graphic_sku = 'NeedGraphicFile';
			$option->allow_mixing = '0';
			$option->batch_route_id = static::getDefaultRouteId();
			$option_array = []; 
			// add the found parameters 
			if ($matches != null) {
				foreach ( $matches as $match ) { 
					$option_array[ static::htmlFormNameToText($match) ] = $item_options[ $match ]; 
				} 
			}
			$option->parameter_option = json_encode($option_array); 
			$option->save(); 
			$option->save();
			
			Inventory::saveinventoryUnit($child_sku, "ToBeAssigned", '1');
		} 
		
		return $child_sku;
	}
	
	public static function generateUniqueRowId ()
	{
		return sprintf("%s_%s", strtotime("now"), str_random(5));
	}

	public static function specialCharsRemover ($text)
	{
		$specialChars = [
			':',
			'&nbsp;',
		];

		return str_replace($specialChars, "", trim($text));
	}

	public static function crawledOptionValueSplitter ($options)
	{
		return array_filter($options, function ($value) {
			return strtolower(trim($value['text'])) !== "please select";
		});
	}

	public static function getOnlyValuesByKey ($data, $key)
	{
		$values = array_map(function ($node) use ($key) {
			return $node[ $key ];
		}, $data);

		return array_combine($values, $values);
	}

	public static function generateChildSKUCombination (array $data, array &$all = [], array $group = [], $value = null, $i = 0)
	{
		$keys = array_keys($data);
		if ( isset($value) === true ) {
			#$value = str_replace(" ", "", strtolower($value));
			array_push($group, $value);
		}
		if ( $i >= count($data) ) {
			$array = [
				'nodes'      => $group,
				'suggestion' => implode("-", array_map(function ($value) {
					return $value = str_replace(" ", "", strtolower($value));
				}, $group)),
			];
			array_push($all, $array);
		} else {
			$currentKey = $keys[ $i ];
			$currentElement = $data[ $currentKey ];
			foreach ( $currentElement as $val ) {
				static::generateChildSKUCombination($data, $all, $group, $val, $i + 1);
			}
		}

		return $all;
	}

	public static function getEmptyStation ()
	{
		$routes = BatchRoute::with('stations_count')
							->where('is_deleted', 0)
							->get();
		$zeroStations = $routes->filter(function ($row) {
			// if the stations count == 0
			return count($row->stations_count) == 0;
		});

		return $zeroStations;
	}
	
	public static function getTrackingUrl ($trackingNumber)
	{
		if ( isset($trackingNumber[0]) ) {
			if ( substr($trackingNumber, 0, 3) == '927' || $trackingNumber[0] == '8' ) {
				// UPS
				return url(sprintf("https://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%s", $trackingNumber));
			} elseif ( $trackingNumber[0] == 'L' || $trackingNumber[0] == 'U') {
				// USPS
				return url(sprintf("https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=%s", $trackingNumber));
			} elseif ( substr($trackingNumber, 0, 3) == '937' ) {
				//DHL
				return url(sprintf("http://webtrack.dhlglobalmail.com/?trackingnumber=%s", $trackingNumber));
			} elseif ( substr($trackingNumber, 0, 2) == '94' || substr($trackingNumber, 0, 3) == '927' ) {
				//usps
				return url(sprintf("https://tools.usps.com/go/TrackConfirmAction?tLabels=%s", $trackingNumber));
			} elseif ( substr($trackingNumber, 0, 1) == '2' || substr($trackingNumber, 0, 1) == '7' ) {
				//Fedex
				return url(sprintf("http://www.fedex.com/Tracking?tracknumbers=%s", $trackingNumber));
			} elseif ( substr($trackingNumber, 0, 2) == '1Z') {
				//Fedex
				return url(sprintf("http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%s", $trackingNumber));
            } elseif ( substr($trackingNumber, 0, 2) == '42') {
                //DHL
                return url(sprintf("https://webtrack.dhlecs.com/?trackingnumber=%s", $trackingNumber));
            } else {
				return '#';
			}
		}
	}

	public static function generate_xml_from_array ($array, $node_name)
	{
		$xml = '';

		if ( is_array($array) || is_object($array) ) {
			foreach ( $array as $key => $value ) {
				if ( is_numeric($key) ) {
					$key = $node_name;
				}

				$xml .= '<' . $key . '>' . "\n" . Helper::generate_xml_from_array($value, $node_name) . '</' . $key . '>' . "\n";
			}
		} else {
			$xml = htmlspecialchars($array, ENT_QUOTES) . "\n";
		}

		return $xml;
	}

	public static function generate_valid_xml_from_array ($array, $node_block = 'nodes', $node_name = 'node')
	{
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

		$xml .= '<' . $node_block . '>' . "\n";
		$xml .= Helper::generate_xml_from_array($array, $node_name);
		$xml .= '</' . $node_block . '>' . "\n";

		return $xml;
	}

	public static function removeSpecial ($string)
	{
		//$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		return preg_replace('/[^A-Za-z0-9,\-]/', ' ', $string); // Removes special chars.
	}


    public function shopify_call($api_endpoint, $query = array(), $method = 'GET', $request_headers = array())
    {
        $token ="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";
        $shop = "monogramonline";

        // Build URL
        $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
        if (!is_null($query) && in_array($method, array('GET', 'DELETE'))) $url = $url . "?" . http_build_query($query);

        // Configure cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
        // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Setup headers
        $request_headers[] = "";
        if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) $query = http_build_query($query);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }

        // Send request to Shopify and capture any errors
        $response = curl_exec($curl);
        $error_number = curl_errno($curl);
        $error_message = curl_error($curl);

        // Close cURL to be nice
        curl_close($curl);

        // Return an error is cURL has a problem
        if ($error_number) {
            return $error_message;
        } else {

            // No error, return Shopify's response by parsing out the body and the headers
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

            ####################
            if($api_endpoint === "/admin/api/2020-04/discount_codes/lookup.json"){

//                $location = [];
                $header_data = explode("\r\n", $response[1]);

//                foreach ($header_data as $part) {
//                     if (strpos(urldecode($part), 'admin/price_rules') !== false) {
//                         $location = explode("%2F", $part);
//                     }
//                }

                $location = json_decode(array_pop($header_data), true);

                if(isset($location['discount_code'])){
                    return array('headers' => [], 'response' => $location);
                }else{
                    return array('headers' => [], 'response' => []);
                }

            }
            ####################

            // Convert headers into an array
            $headers = array();
            $header_data = explode("\n", $response[0]);
            $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
            array_shift($header_data); // Remove status, we've already set it above
            foreach ($header_data as $part) {
                $h = explode(":", $part);
                $headers[trim($h[0])] = trim($h[1]);
            }

            // Return headers and Shopify's response
            return array('headers' => $headers, 'response' => $response[1]);
        }
    }

    public function getUrlWithoutParaMeter($url){
        $url_parts = parse_url($url);
        if(isset($url_parts['scheme'])){
            $constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
        }else{
            $constructed_url = $url;
        }
        return $constructed_url;
    }

    public function optionsValuesFilter($string) {
        #$string = str_replace(' ', '-', $string);
//        $this->jdbg("Before Value =", $string);
        $string = explode("+",$string);

//        $string = preg_replace("/[^A-Za-z0-9\- &.@'!,$*]/',", trim($string[0]));
//        $string = preg_replace('/[^A-Za-z0-9\- .@!,$*]/', '', trim($string[0]));
        $string = preg_replace('/[^A-Za-z0-9\-.&@"!,$* \'()]/', '', trim($string[0]));
//        $this->jdbg("After Value =", $string);
//        Log::info("---------------------------------------------------------------------------------");
        return $string;
    }

    public function isKeyExist($sku, $keyString, $value)
    {

        //$k = str_replace(['Choose ', 'Select '], '', substr($key, $len));
//            Log::info("isKeyExist key = ".$keyString." -> value = ".$value);

        $restrictedArray = [
            "I've reviewed my design. Everything is correct.",
            "I've reviewed my design. Everything is correct.%0D%0A",
            "_pplr_preview",
            "Preview",
            "_Photo_crop",
            "_font size PERSONALIZATION",
            "_pc_pricing_ref",
            "_pc_pricing_qty",
            "_pc_pricing_origin",
            "_pc_pricing_qty_split",
        ];
//        Log::info($sku." => ".$keyString);
        if (in_array($keyString, $restrictedArray)) {
            return true;
        } else {
            return false;
        }
    }

    public function jdbg($label, $obj)
    {
        $logStr = "5p -- {$label}: ";
        switch (gettype($obj)) {
            case 'boolean':
                if($obj){
                    $logStr .= "(bool) -> TRUE";
                }else{
                    $logStr .= "(bool) -> FALSE";
                }
                break;
            case 'integer':
            case 'double':
            case 'string':
                $logStr .= "(" . gettype($obj) . ") -> {$obj}";
                break;
            case 'array':
                $logStr .= "(array) -> " . print_r($obj, true);
                break;
            case 'object':
                try {
                    if (method_exists($obj, 'debug')) {
                        $logStr .= "(" . get_class($obj) . ") -> " . print_r($obj->debug(), true);
                    } else {
                        $logStr .= "Don't know how to log object of class " . get_class($obj);
                    }
                } catch (Exception $e) {
                    $logStr .= "Don't know how to log object of class " . get_class($obj);
                }
                break;
            case 'NULL':
                $logStr .= "NULL";
                break;
            default:
                $logStr .= "Don't know how to log type " . gettype($obj);
        }

        Log::info($logStr);
    }

}