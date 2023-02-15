<?php namespace Monogram;

use App\EmailTemplate;
use App\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class TemplateExtractor
{
	private $order = null;
	private $template = null;
	
	public function getMessage($order, $template, $param_1 = null, $param_2 = null) 
	{	
		$this->order = null;
		$this->template = null;
		
		$this->order = $order;
		
		$this->template = EmailTemplate::find($template);
		
		$subject = $this->subjectBuilder();
		
		if ( $this->template->type == 'view' ) {
			
			$message_body = View::make($this->template->message, ['order' => $this->order, 'store' => $this->order->store])->render();
			
		} else if ( $this->template->type == 'message' ) { 
			
			$css = "<style>body,td,th { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; margin-top: 0px; } " .
					"h2 { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 18px; font-weight:bold; }</style>";
					
			$message_body = $css . $this->messageBuilder(str_replace('**1**', $param_1, str_replace('**2**', $param_2, $this->template->message)));
		}
		
		return ['subject' => $subject, 'message' => $message_body, 'message_type' => $this->template->message_type];
	}

	private function messageBuilder ($msg)
	{
		return $this->stringParser($msg);
	}

	private function subjectBuilder ()
	{
		return $this->stringParser($this->template->message_title);
	}

	private function stringParser ($string)
	{
		$pattern = implode("|", array_keys(self::$EMAIL_TEMPLATE_KEYWORDS));
		// escape the string
		$pattern = str_replace(array_keys($this->REGEX_ESCAPES), array_values($this->REGEX_ESCAPES), $pattern);
		$pattern = sprintf("~%s~", $pattern);
		$parsed = preg_replace_callback($pattern, [
			$this,
			"processor",
		], $string);

		return $parsed;
	}

	private function processor ($match)
	{
		$found = $match[0];
		if ( array_key_exists($found, self::$EMAIL_TEMPLATE_KEYWORDS) ) {
			// get the value at index 1 on email template
			$relations = self::$EMAIL_TEMPLATE_KEYWORDS[$found][1];
			// if relation as string
			if ( is_string($relations) ) {
				return $this->extractRelationInformation($relations);
				// given as array,
				// multiple relation
			} elseif ( is_array($relations) ) {
				$extracted_relation = [ ];
				foreach ( $relations as $relation ) {
					$extracted_relation[] = $this->extractRelationInformation($relation);
				}
				// before joining the array,
				// filter down the empty results
				#return implode(", ", array_filter($extracted_relation));
				return implode(", ", $extracted_relation);
			}

			return $relations;
		}

		return $found;
	}

	private function extractRelationInformation ($relation)
	{
		// explode the string based on dot
		$parts = explode(".", $relation);
		$data = $this->order;
		
		foreach ( array_slice($parts, 1) as $part ) {
			// keep extracting the data until the final data is found
			$data = $data->$part;
		}
		
		return $data;
	}
	
	private $REGEX_ESCAPES = [
		'.' => '\.',
	];

	private $EMAIL_TEMPLATE_SPECIAL_KEYWORDS = [
		'@@STORECTOT@@' => [
			'Total storecredit a/v to the customer',
			'---',
		],
		'@@STOREC@@'    => [
			'Store credit added',
			'---',
		],
		'@@STORECREF@@' => [
			'Reference added to the store credit by the user',
			'---',
		],
		'@@REFUND@@'    => [
			'Refund amount',
			'---',
		],
		'@@CCR@@'       => [
			'Payment method used for the refund',
			'---',
		],
	];

	public static $EMAIL_TEMPLATE_KEYWORDS = [
		/*'TEMPLATE-KEY'           => [
			'replaceable-value-on-view',
			'replaceable-value-on-code-relationship-or-closure',
		],*/
		'@@STORENAME@@'          => [
			'store name',
			'order.store.store_name',
		],
		'@@NAME@@'               => [
			'customer name',
			'order.customer.ship_full_name',
		],
		'@@B_NAME@@'             => [
			'billed customer name',
			'order.customer.bill_full_name',
		],
		'@@FIRST@@'              => [
			'customer first name',
			'order.customer.ship_first_name',
		],
		'@@LAST@@'               => [
			'customer last name',
			'order.customer.ship_last_name',
		],
		'@@ID@@'                 => [
			'order Id',
			'order.order_id',
		],
		'@@IDS@@'                => [
			'short order Id',
			'order.short_order',
		],
		/*'@@4PID@@'               => [
			'4P order #',
			'customer.full_name',
		],*/
		'@@ODATE@@'              => [
			'Order date',
			'order.order_date',
		],
		'@@COMPANY@@'            => [
			'company name',
			'order.store.store_name',
		],
		/*'@@SIGN@@'               => [
			'Contact name',
			'customer.full_name',
		],*/
		'@@URL@@'                => [
			'Company main domain',
			'order.store_name',
		],
		/*'@@EMAIL@@'              => [
			'Customer support email',
			'-------',
		],*/
		/*'@@PHONE@@'              => [
			'company phone',
			'-------',
		],*/
		/*'@@RMA@@'                => [
			'Order RMA',
			'-------',
		],*/
		'@@ShipTo.FullAddress@@' => [
			'Full shipping address',
			[
				'order.customer.ship_address_1',
				'order.customer.ship_address_2',
				'order.customer.ship_city',
				'order.customer.ship_state',
				'order.customer.ship_zip',
				'order.customer.ship_country',
				'order.customer.ship_phone',
			],
		],
		'@@BillTo.FullAddress@@' => [
			'Full billing address',
			[
				'order.customer.bill_address_1',
				'order.customer.bill_address_2',
				'order.customer.bill_city',
				'order.customer.bill_state',
				'order.customer.bill_zip',
				'order.customer.bill_country',
				'order.customer.bill_phone',
			],
		],
		'@@Lines.Summary@@'      => [
			'order lines & summary',
			'-------',
		],
		'@@Lines.Only@@'         => [
			'order lines',
			'-------',
		],
		'@@Lines.Only.BO@@'      => [
			'order lines that are on b/o',
			'-------',
		],
		'@@Lines.Only.NP@@'      => [
			'order lines that w/o price',
			'-------',
		],
		'@@USERNAME@@'           => [
			'User\'s name',
			'-------',
		],
		'@@DATE@@'               => [
			'Email date',
			'-------',
		],
		'@@SHIPMETHOD@@'         => [
			'Order ship method',
			'order.customer.shipping',
		],
		'@@CC@@'                 => [
			'Credit Card #',
			'-------',
		],
		'@@EXPIRE@@'             => [
			'CC expiration date',
			'-------',
		],
		'@@RETVAL@@'             => [
			'Return total',
			'-------',
		],
		'@@COMPADDR@@'           => [
			'Company address',
			'-------',
		],
		'@@TRK@@'                => [
			'order trk#',
			'-------',
		],
		'@@ORDERTOTAL@@'         => [
			'order total',
			'-------',
		],
		'@@GIFTWRAPMESSAGE@@'    => [
			'Gift message',
			'-------',
		],
		'@@SHIPPHONE@@'          => [
			'Ship to phone',
			'-------',
		],
		'@@LOGO@@'               => [
			'store/company logo',
			'-------',
		],
		'@@COMM@@'               => [
			'customer comments',
			'-------',
		],
		'@@CEMAIL@@'             => [
			'customer\'s email',
			'-------',
		],
		'@@ITEM@@'               => [
			'Product SKU/Name',
			'-------',
		],
		'@@ITEMCODE@@'           => [
			'Product SKU/Code',
			'-------',
		],
		'@@ITEMNAME@@'           => [
			'Item name',
			'-------',
		],
		/*'---'                    => [
			'-------',
			'-------',
		],*/
	];
}
