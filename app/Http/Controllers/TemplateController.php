<?php

namespace App\Http\Controllers;

use App\Template;
use App\TemplateOption;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

class TemplateController extends Controller
{
	private $repeated_fields = [
		1 => 'Line item fields are on new lines',
		2 => 'Header - Line item fields - Footer',
		3 => 'M.O.M. - leave all fields empty',
		4 => 'BETA - VendorNet - leave all fields empty',
		5 => 'Line item fields are on the same line',
	];

	private $delimited_chars = [
		1 => 'TAB',
		2 => 'Comma',
		3 => 'Pipe',
		4 => 'Comma/Unquoted',
		5 => 'Space',
	];

	private $formats = [
		1  => 'Integer',
		2  => 'Float',
		3  => 'Date, MM/DD/YYYY',
		4  => 'Date, DD/MM/YYYY',
		5  => 'Date, YYYY-MM-DD',
		6  => 'Date, yyyyMMdd',
		7  => 'Date, MM/DD/YYYY + 5 days',
		8  => 'Strip phone #',
		9  => 'Strip prefix',
		10 => 'Text/Excel',
		11 => 'Integer, add 1',
		12 => 'Extract option value',
		13 => 'Extract custom value',
		14 => 'Date, MM/DD/YYYY + X days',
		15 => 'Current Date, MM/DD/YYYY',
		16 => 'To UpperCase',
		17 => 'Date, MM-DD-YY HH:MM',
		18 => 'Date, YYYY-MM-DD HH:MM:SS',
		19 => 'Text',
	];

	private $option_categories = [
		'FIX'                                       => 'Fixed value',
		'header.currentdate'                        => 'Current date',
		'header.billing.address.firstname'          => 'bill First name',
		'header.billing.address.lastname'           => 'bill Last name',
		'header.ordernumber'                        => 'Order number',
		'header.4plordernumber'                     => '4PL Order number',
		'header.longordernumber'                    => 'Full Order number',
		'header.prefixordernumber'                  => 'D/S prefix & Order number',
		'header.po_num'                             => 'PO number',
		'header.store'                              => 'Store',
		'header.storeType'                          => 'store Type',
		'header.orderdate'                          => 'order Date',
		'header.saletax.amount'                     => 'order Tax',
		'header.saletax.rate'                       => 'Sales Tax Percent',
		'header.shippingamount.amount'              => 'shipping Cost',
		'header.subtotal'                           => 'order Total',
		'header.giftwrap'                           => 'Gift wrap charge',
		'header.custNum'                            => 'Customer Number',
		'header.email'                              => 'email',
		'header.comments'                           => 'Order comments',
		'header.orderCustom'                        => 'Gift Message',
		'header.customData'                         => 'Custom fields',
		'header.orderStatus'                        => 'Order status code',
		'header.billing.email'                      => 'email',
		'header.billing.address.company'            => 'Bill Company name',
		'header.billing.address.Bill_Name'          => 'Bill Name',
		'header.billing.address.line1'              => 'bill Address1',
		'header.billing.address.line2'              => 'bill Address2',
		'header.billing.address.city'               => 'bill City',
		'header.billing.address.state'              => 'bill State',
		'header.billing.address.postalcode'         => 'bill Zip',
		'header.billing.address.country'            => 'bill Country',
		'header.billing.address.scountry'           => 'bill Country (short)',
		'header.billing.address.fcountry'           => 'bill Country (long)',
		'header.billing.address.phonenumber'        => 'bill Phone',
		'header.billing.payment.method'             => 'creditCard',
		'header.billing.payment.amount'             => 'orderTotal',
		'header.shipping.address.company'           => 'Ship Company name',
		'header.shipping.shippingmethod.carrier'    => 'ship - carrier',
		'header.shipping.shippingmethod.methodname' => 'shipMethod name',
		'header.shipping.shippingmethod.code'       => 'shipMethod code',
		'header.shipping.address.firstname'         => 'ship First name',
		'header.shipping.address.lastname'          => 'ship Last name',
		'header.shipping.address.Ship_Name'         => 'Ship Name',
		'header.shipping.address.line1'             => 'ship Address1',
		'header.shipping.address.line2'             => 'ship Address2',
		'header.shipping.address.city'              => 'ship City',
		'header.shipping.address.state'             => 'ship State',
		'header.shipping.address.postalcode'        => 'ship Zip',
		'header.shipping.address.country'           => 'ship Country',
		'header.shipping.address.scountry'          => 'ship Country (short)',
		'header.shipping.address.fcountry'          => 'ship Country (long)',
		'header.shipping.address.phonenumber'       => 'ship Phone',
		'header.numlines'                           => '# of lines',
		'items.itemID'                              => 'Item ID',
		'items.sku'                                 => 'Item SKU',
		'items.upc'                                 => 'Item UPC',
		'items.vendorSku'                           => 'Supplier SKU',
		'items.description'                         => 'Item name',
		'items.unitprice'                           => 'Item unit price',
		'items.itemCost'                            => 'Item unit cost',
		'items.quantity'                            => 'Items Qty',
		'items.itemOptions'                         => 'Item Options',
		'items.shipperOptions'                      => 'Item Shipper Notes',
		'items.lineItem'                            => 'Item Line #',
		'items.lineseq'                             => 'SEQ #, starts at 0',
		'items.nameOptions'                         => 'Item name & options',
		'items.subtotal'                            => 'Line item sub-total',
	];

	public function index ()
	{
		$templates = Template::where('is_deleted', 0)
							 ->latest()
							 ->paginate(50);
		$count = 1;

		return view('templates.index', compact('templates', 'count'));
	}

	public function store (Request $request)
	{
		$template_name = $request->get('template_name');
		if ( !$template_name ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([ 'error' => 'Template name cannot be empty', ]));
		}
		$template = new Template();
		$template->template_name = $request->get('template_name');
		$template->save();

		return redirect(url('/prod_config/templates'));
	}

	public function show ($id)
	{
		$template = Template::with('options')
							->find($id);

		if ( !$template ) {
			return view('errors.404');
		}
		$repeated_fields = $this->repeated_fields;
		$delimited_chars = $this->delimited_chars;
		$formats = $this->formats;
		$option_categories = $this->option_categories;

		#return $template;
		return view('templates.show', compact('template', 'repeated_fields', 'delimited_chars', 'options', 'formats', 'option_categories'));
	}

	public function edit ($id)
	{
		// item_taxable
	}

	public function update (Request $request, $id)
	{
#dd($request->all());		
		$template = Template::find($id);

		if ( !$template ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([ 'error' => 'Template id is invalid' ]));
		}

		$template->template_name = $request->get('template_name');
		$template->repeated_fields = $request->get('repeated_fields');
		$template->delimited_char = $request->get('delimited_char');
		$template->show_header = $request->get('show_header');
		$template->break_kits = $request->get('break_kits');
		$template->is_active = $request->get('is_active');
		$template->save();

		$index = 0;
		$line_item_fields = $request->get('line_item_fields');
		$option_names = $request->get('option_name');
		$option_categories = $request->get('option_category');
		$values = $request->get('value');
		$widths = $request->get('width');
		$formats = $request->get('format');
		$template_orders = $request->get('template_orders');

		TemplateOption::where('template_id', $id)
					  ->delete();

		$errors = false;

		foreach ( $option_names as $option_name ) {
			if ( !trim($option_names[$index]) ) {
				$errors = true;
				continue;
			}
			$templateOptions = new TemplateOption();
			$templateOptions->template_id = $id;
			$templateOptions->line_item_field = $line_item_fields[$index];
			$templateOptions->option_name = trim($option_names[$index]);
			$templateOptions->option_category = $option_categories[$index];
			$templateOptions->value = trim($values[$index], ",");
			$templateOptions->width = $widths[$index];
			$templateOptions->format = $formats[$index];
			$templateOptions->template_order = $template_orders[$index];
			$templateOptions->save();

			$index++;
		}

		Session::flash('success', sprintf("Template <b>%s</b> is successfully updated", $template->template_name));
		$messageBag = null;
		if ( $errors ) {
			$messageBag = new MessageBag([
				'error' => 'Option name was left empty. Was not added to the template.',
			]);
		}

		return redirect(url('/prod_config/templates/' . $id))->withErrors($messageBag);
	}

	public function destroy ($id)
	{
		$template = Template::find($id);

		if ( !$template ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([ 'error' => 'Template id is invalid' ]));
		}

		$template->is_deleted = 1;
		$template->save();

		return redirect(url('/prod_config/templates'));
	}
}
