<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Product Spec sheet - 2</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		tbody#product-specification-table-body tr td input {
			width: 100px;
		}

		tbody#product-specification-table-body tr td .wide-input {
			width: 210px;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/products_specifications')}}">Product specifications</a></li>
			<li>Product specifications step- 2</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{!! Form::open(['url' => url('/products_specifications/step/2'), 'method' => 'post', 'files' => true, 'novalidate']) !!}
		<div class = "col-md-12">
			<ul class = "nav nav-tabs" role = "tablist">
				<li role = "presentation" class = "active">
					<a href = "#tab-general" aria-controls = "general" role = "tab"
					   data-toggle = "tab">General info</a>
				</li>
				<li role = "presentation">
					<a href = "#tab-specification" aria-controls = "specification" role = "tab"
					   data-toggle = "tab">Product specification</a>
				</li>
				<li role = "presentation">
					<a href = "#tab-instruction" aria-controls = "instruction" role = "tab"
					   data-toggle = "tab">Product instruction</a>
				</li>
				<li role = "presentation">
					<a href = "#tab-note" aria-controls = "note" role = "tab"
					   data-toggle = "tab">Special Note</a>
				</li>
				<li role = "presentation">
					<a href = "#tab-financial" aria-controls = "financial" role = "tab"
					   data-toggle = "tab">Financial info</a>
				</li>
			</ul>

			<!-- Tab panels -->
			<div class = "tab-content" style = "margin-top: 20px;">
				<div role = "tabpanel" class = "tab-pane fade in active" id = "tab-general">
					<div class = "form-group col-md-12">
						{!! Form::label('status', 'Status', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::select('status', \App\SpecificationSheet::$statuses, null, ['id' => 'status', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('web_image_status', 'Web image status', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::select('web_image_status', \App\SpecificationSheet::$webImageStatus, null, ['id' => 'web_image_status', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_images', 'Product images', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::file('product_images[]', ['id' => 'product_images', 'multiple' => true, 'accept' => 'image/*',  'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_name', 'Product name', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('product_name', null, ['id' => 'product_name', 'class' => "form-control"]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_sku', 'Product SKU', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('product_sku', $sku, ['id' => 'product_sku', 'class' => "form-control"/*, 'readonly' => 'readonly'*/]) !!}
						</div>
						<div class = "col-md-4">
							{!! Form::text('previous_sku', null, ['id' => 'previous_sku', 'class' => "form-control", 'placeholder' => 'Enter previous SKU to copy or leave empty']) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_description', 'Product description', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::textarea('product_description', null, ['id' => 'product_description', 'rows' => 4, 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_general_note', 'General note', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::textarea('product_general_note', null, ['id' => 'product_general_note', 'rows' => 4, 'class' => "form-control",]) !!}
						</div>
					</div>
				</div>
				<div role = "tabpanel" class = "tab-pane fade" id = "tab-specification">
					<div class = "form-group col-md-12">
						{!! Form::label('make_sample', 'Make sample?: ', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::select('make_sample', \Monogram\Helper::$specSheetSampleDataArray, "No", ['id' => 'product_weight', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_weight', 'Product Weight (lb) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('product_weight', null, ['id' => 'product_weight', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_length', 'Length (in) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('product_length', null, ['id' => 'product_length', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_width', 'Width (in) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('product_width', null, ['id' => 'product_width', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_height', 'Height (in) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('product_height', null, ['id' => 'product_height', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('packaging_type_name', 'Packaging Type Name :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('packaging_type_name', null, ['id' => 'packaging_type_name', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('packaging_size', 'Packaging size LxWxH (in) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('packaging_size', null, ['id' => 'packaging_size', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('packaging_weight', 'Packaging Weight (lb):', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('packaging_weight', null, ['id' => 'packaging_weight', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('total_weight', 'Total Weight (lb) :', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::number('total_weight', null, ['id' => 'total_weight', 'steps' => 'any', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "col-md-12">
						<table class = "table">
							<caption>Product purchase information</caption>
							<thead>
							<tr>
								<th>SL</th>
								<th>Parts Name</th>
								<th>Supplier SKU #</th>
								<th>Supplier Name</th>
								<th>Supplier URL</th>
								<th>Material</th>
								<th>Size</th>
								<th>Color</th>
								<th>Type</th>
								<th>Price</th>
								<th>Note</th>
								<th></th>
							</tr>
							</thead>
							<tbody id = "product-specification-table-body">
							<tr>
								<td>
									1.
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::select('spec_table_data[]', $vendors, 0, ['class' => 'form-control wide-input']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control wide-input']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<div class = "form-group">
										{!! Form::text('spec_table_data[]', null, ['class' => 'form-control']) !!}
									</div>
								</td>
								<td>
									<a class = "btn btn-link remove-row">Remove</a>
									<a class = "btn btn-link copy-row">Copy</a>
								</td>
							</tr>
							</tbody>
							<tfoot>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td>
									<a class = "btn btn-block btn-success add-new-spec-row">Add new row</a>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div role = "tabpanel" class = "tab-pane fade" id = "tab-instruction">
					<div class = "form-group col-md-12">
						{!! Form::label('production_category', 'Production category', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::select('production_category', $production_categories, $selected_production_category, ['id' => 'production_category', 'class' => "form-control"]) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('production_image_location', 'Production image location', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('production_image_location', null, ['id' => 'production_image_location', 'class' => 'form-control']) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('art_work_location', 'Art work image location', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::text('art_work_location', null,['id' => 'art-work-location', 'class' => 'form-control']) !!}
						</div>
					</div>
					<div class = "form-group col-md-12">
						{!! Form::label('product_details_file', 'Product details file', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::file('product_details_file[]', ['id' => 'product_details_file', 'accept' => 'image/*',  'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "col-md-12">
						<div class = "col-md-6">
							<table class = "table">
								<caption class = "text-center">Production Settings</caption>
								<tbody>
								<tr>
									<td>Temperature</td>
									<td>
										<div class = "form-group">
											{!! Form::text('temperature', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Dwell time</td>
									<td>
										<div class = "form-group">
											{!! Form::text('dwell_time', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Pressure</td>
									<td>
										<div class = "form-group">
											{!! Form::text('pressure', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Run Time</td>
									<td>
										<div class = "form-group">
											{!! Form::text('run_time', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>

						<div class = "col-md-6">
							<table class = "table">
								<caption class = "text-center">Personalization Details</caption>
								<tbody>
								<tr>
									<td>Type</td>
									<td>
										<div class = "form-group">
											{!! Form::text('type', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Font</td>
									<td>
										<div class = "form-group">
											{!! Form::textarea('font', null, ['class' => 'form-control', 'rows' => 6]) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Variation name</td>
									<td>
										<div class = "form-group">
											{!! Form::text('variation_name', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								<tr>
									<td>Details</td>
									<td>
										<div class = "form-group">
											{!! Form::text('details', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						<div class = "col-md-12">
							<table class = "table">
								<thead>
								<tr>
									<th>Special Notes for Product Options</th>
									<th>Option Name</th>
									<th>Details</th>
								</tr>
								</thead>
								<tbody id = "product-instruction-table-body">
								<tr>
									<td>
										<div class = "form-group">
											{!! Form::text('special_note[]', null, ['class' => 'form-control']) !!}
										</div>
									</td>
									<td>
										<div class = "form-group">
											{!! Form::text('option_name[]', null, ['class' => 'form-control']) !!}
										</div>
									</td>
									<td>
										<div class = "form-group">
											{!! Form::text('details[]', null, ['class' => 'form-control']) !!}
										</div>
									</td>
								</tr>
								</tbody>
								<tfoot>
								<tr>
									<td></td>
									<td></td>
									<td>
										<div class = "form-group">
											{!! Form::button('Add new row', ['id' => 'add-new-instruction-row', 'class' => 'btn btn-success btn-block']) !!}
										</div>
									</td>
								</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				<div role = "tabpanel" class = "tab-pane fade" id = "tab-note">
					<div class = "form-group col-md-12">
						{!! Form::label('product_note', 'Product note', ['class' => 'col-md-2 control-label']) !!}
						<div class = "col-md-4">
							{!! Form::textarea('product_note', null, ['id' => 'product_note', 'rows' => 4, 'class' => "form-control",]) !!}
						</div>
					</div>
				</div>
				<div role = "tabpanel" class = "tab-pane fade" id = "tab-financial">
					<table class = "table">
						<tr>
							<td>Qty, pcs</td>
							<td>1</td>
							<td>10</td>
							<td>100</td>
							<td>1000</td>
							<td>10000</td>
						</tr>
						<tr>
							<td>Cost, $</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('cost_of_1', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('cost_of_10', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('cost_of_100', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('cost_of_1000', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('cost_of_10000', null, ['class' => 'form-control']) !!}
								</div>
							</td>
						</tr>
					</table>
					<table class = "table" id = "cost-variable-table">
						<thead>
						<tr>
							<th>Parts Name</th>
							<th>Cost Variation 1, $</th>
							<th>Cost Variation 2, $</th>
							<th>Cost Variation 3, $</th>
							<th>Cost Variation 4, $</th>
							<th>Supplier Name</th>
							<th></th>
						</tr>
						</thead>
						<tbody id = "cost-variable-table-body">
						<tr>
							<td>
								<div class = "form-group">
									{!! Form::text('parts_name[]', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('cost_variation[]', null, ['class' => 'form-control cost-variation-1']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('cost_variation[]', null, ['class' => 'form-control cost-variation-2']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('cost_variation[]', null, ['class' => 'form-control cost-variation-3']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('cost_variation[]', null, ['class' => 'form-control cost-variation-4']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('supplier_name[]', null, ['class' => 'form-control']) !!}
								</div>
							</td>
							<td>
								<a href = "" class = "btn btn-link remove-parts-info-row">Remove</a>
							</td>
						</tr>
						</tbody>
						<tfoot id = "cost-variable-table-footer">
						<tr>
							<td>Total (SUM), $</td>
							<td class = "cost-variation-1-total">0.0</td>
							<td class = "cost-variation-2-total">0.0</td>
							<td class = "cost-variation-3-total">0.0</td>
							<td class = "cost-variation-4-total">0.0</td>
							<td>
								<div class = "form-group">
									{!! Form::button('Add new row', ['id' => 'add-new-row', 'class' => 'btn btn-success btn-block']) !!}
								</div>
							</td>
						</tr>
						<tr>
							<td>Delivery to us, $</td>
							<td>
								<div class = "form-group">
									{!! Form::text('delivery_cost_variation[]', null, ['class' => 'form-control cost-variation-1']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('delivery_cost_variation[]', null, ['class' => 'form-control cost-variation-2']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('delivery_cost_variation[]', null, ['class' => 'form-control cost-variation-3']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('delivery_cost_variation[]', null, ['class' => 'form-control cost-variation-4']) !!}
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>Labor Expense to complete Production Process, $</td>
							<td>
								<div class = "form-group">
									{!! Form::text('labor_expense_cost_variation[]', null, ['class' => 'form-control cost-variation-1']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('labor_expense_cost_variation[]', null, ['class' => 'form-control cost-variation-2']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('labor_expense_cost_variation[]', null, ['class' => 'form-control cost-variation-3']) !!}
								</div>
							</td>
							<td>
								<div class = "form-group">
									{!! Form::text('labor_expense_cost_variation[]', null, ['class' => 'form-control cost-variation-4']) !!}
								</div>
							</td>
							<td></td>
						</tr>
						</tfoot>
					</table>
					<table class = "table">
						<tr>
							<td>Total Cost Variation 1</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('sum_of_cost_variation_1[]', null, ['class' => 'form-control sum_of_cost_variation_1']) !!}
								</div>
							</td>
						</tr>
						<tr>
							<td>Total Cost Variation 2</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('sum_of_cost_variation_2[]', null, ['class' => 'form-control sum_of_cost_variation_2']) !!}
								</div>
							</td>
						</tr>
						<tr>
							<td>Total Cost Variation 3</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('sum_of_cost_variation_3[]', null, ['class' => 'form-control sum_of_cost_variation_3']) !!}
								</div>
							</td>
						</tr>
						<tr>
							<td>Total Cost Variation 4</td>
							<td>
								<div class = "input-group">
									<span class = "input-group-addon" id = "basic-addon1">$</span>
									{!! Form::text('sum_of_cost_variation_4[]', null, ['class' => 'form-control sum_of_cost_variation_4']) !!}
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class = "form-group">
			<div class = "col-md-2 pull-right">
				{!! Form::submit('Create',['class'=>'btn btn-primary btn-block']) !!}
			</div>
		</div>
		{!! Form::close() !!}
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		$("#add-new-row").on("click", function (e)
		{
			e.preventDefault();
			var clone = $("#cost-variable-table-body tr:last").clone().find('input').val('').end().insertAfter("#cost-variable-table-body tr:last");
		});

		$("#add-new-instruction-row").on('click', function (e)
		{
			e.preventDefault();
			var clone = $("#product-instruction-table-body tr:last").clone().find('input').val('').end().insertAfter("#product-instruction-table-body tr:last");
		});
		$(document).on('keyup', '#cost-variable-table-body input', function (e)
		{
			e.preventDefault();
			var classes = $(this).attr('class').split(" ");
			if ( classes.length > 1 ) {
				var column_class = classes[1];
				var sum = getTotalCostVariation(column_class);
				/*
				 * CLASS for column: cost-variation-1
				 * CLASS for  total: cost-variation-1-total
				 */
				$("#cost-variable-table-footer td." + column_class + "-total").html(sum);
				setSumOfCostVariation();
			}
		});

		$(document).on('keyup', '#cost-variable-table-footer input', function (e)
		{
			e.preventDefault();
			setSumOfCostVariation();
		});

		$(document).on('click', "a.remove-row", function (event)
		{
			event.preventDefault();
			$(this).closest('tr').remove();
			regenerateSerial();
		});

		$(document).on('click', "a.remove-parts-info-row", function (e)
		{
			event.preventDefault();
			$(this).closest('tr').remove();
		});

		$(document).on('click', "a.copy-row", function (event)
		{
			event.preventDefault();
			var clone = $(this).closest('tr').clone().insertAfter($(this).closest('tbody').find('tr:last'));
			regenerateSerial();
		});

		$("a.add-new-spec-row").click(function (event)
		{
			var clone = $("tbody#product-specification-table-body tr:last").clone().find('input').val('').end().insertAfter("tbody#product-specification-table-body tr:last");
			regenerateSerial();
		});

		function regenerateSerial ()
		{
			var node = "tbody#product-specification-table-body";
			var i = 1;
			$(node).find('tr').each(function ()
			{
				// TODO: none remove able first row
				/*if ( i == 1 ) {
				 $(this).find('a.remove-row').first().disable();
				 }*/
				$(this).find('td').eq(0).text(i++);
			});

			nonRemoveAbleFirstRow(node);
		}

		function nonRemoveAbleFirstRow (parentNode)
		{
			$(parentNode).find('tr').first().find('a.remove-row').first().remove();
		}

		function setSumOfCostVariation ()
		{
			var sum = 0.0;
			$(".cost-variation-1").each(function ()
			{
				var parsed = parseFloat($(this).val());
				sum += parsed ? parsed : 0.0;
			});

			$(".sum_of_cost_variation_1").val(sum);

			var sum = 0.0;
			$(".cost-variation-2").each(function ()
			{
				var parsed = parseFloat($(this).val());
				sum += parsed ? parsed : 0.0;
			});

			$(".sum_of_cost_variation_2").val(sum);

			var sum = 0.0;
			$(".cost-variation-3").each(function ()
			{
				var parsed = parseFloat($(this).val());
				sum += parsed ? parsed : 0.0;
			});

			$(".sum_of_cost_variation_3").val(sum);

			var sum = 0.0;
			$(".cost-variation-4").each(function ()
			{
				var parsed = parseFloat($(this).val());
				sum += parsed ? parsed : 0.0;
			});

			$(".sum_of_cost_variation_4").val(sum);
		}

		function getTotalCostVariation (class_name)
		{
			var sum = 0.0;
			$("#cost-variable-table-body input." + class_name).each(function ()
			{
				var parsed = parseFloat($(this).val());
				sum += parsed ? parsed : 0.0;
			});

			return sum.toFixed(2);
		}
		$("form").on('submit', function (event)
		{
			var product_name = $("input#product_name").val().trim();
			var previous_sku = $("input#previous_sku").val().trim();
			if ( product_name == "" && previous_sku == "" ) {
				alert('Product name cannot be empty');
				return false;
			} else if ( previous_sku != "" ) {
				return true;
			}
		});
	</script>
</body>
</html>