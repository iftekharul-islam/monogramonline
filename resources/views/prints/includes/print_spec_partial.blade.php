<div>
	@if(json_decode($spec->images))
		<div>
			@foreach(json_decode($spec->images) as $image)
				<img src = "{{$image}}" width = "500" height = "500">
			@endforeach
		</div>
	@endif
</div>

<div>
	<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
		<tr valign = "top">
			<td colspan = "10">
				<table width = "100%" cellpadding = "2" cellspacing = "2" border = "0">
					<tr valign = "top">
						<td align = "left" width = "150 ">Product name</td>
						<td>{{$spec->product_name}}</td>
					</tr>
					<tr valign = "top">
						<td align = "left" width = "150 ">Product SKU</td>
						<td>{{$spec->product_sku}}</td>
					</tr>
					<tr>
						<td align = "left">Product description</td>
						<td>{{$spec->product_description}}</td>
					</tr>
					<tr>
						<td align = "left">Product weight</td>
						<td>{{ $spec->product_weight }}</td>
						<td></td>
					</tr>
					<tr>
						<td align = "left">Product length</td>
						<td colspan = "2">{{ $spec->product_length }}</td>
					</tr>
					<tr valign = "middle">
						<td align = "left">Product width</td>
						<td colspan = "2"
						    align = "left">{{ $spec->product_width }}</td>
					</tr>
					<tr>
						<td align = "left">Product height</td>
						<td>{{ $spec->product_height }}</td>
					</tr>
					<tr>
						<td align = "left">Product packing type name</td>
						<td>{{ $spec->packaging_type_name }}</td>
					</tr>
					<tr>
						<td align = "left">Product packing size</td>
						<td>{{ $spec->packaging_size }}</td>
					</tr>
					<tr>
						<td align = "left">Product packing weight</td>
						<td>{{ $spec->packaging_weight }}</td>
					</tr>
					<tr>
						<td align = "left">Product total weight</td>
						<td>{{ $spec->total_weight }}</td>
					</tr>
					<tr>
						<td align = "left">Production category</td>
						<td>{{ $spec->production_category->production_category_code }} : {{ $spec->production_category->production_category_description }}</td>
					</tr>
					<tr>
						<td align = "left">Production image location</td>
						<td>{{ $spec->production_image_location }}</td>
					</tr>
					<tr>
						<td align = "left">Product art work image location</td>
						<td>{{ $spec->art_work_location }}</td>
					</tr>
					<tr>
						<td align = "left">Status</td>
						<td>{{ \App\SpecificationSheet::$statuses[$spec->status] }}</td>
					</tr>
					<tr>
						<td align = "left">Product note</td>
						<td>{{ $spec->product_note }}</td>
					</tr>
					<tr>
						<td align = "left">Make sample?</td>
						<td>{{ $spec->make_sample }}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
					<caption>Product purchase information</caption>
					<tr valign = "top">
						<th align = "center" style = "width:6mm;">SL #</th>
						<th align = "center" style = "width:6mm;">Parts Name</th>
						<th align = "center" style = "width:6mm;">Supplier SKU #</th>
						<th align = "center" style = "width:6mm;">Supplier Name</th>
						<th align = "center" style = "width:6mm;">Supplier URL</th>
						<th align = "center" style = "width:6mm;">Material</th>
						<th align = "center" style = "width:6mm;">Size</th>
						<th align = "center" style = "width:6mm;">Color</th>
						<th align = "center" style = "width:6mm;">Type</th>
						<th align = "center" style = "width:6mm;">Price</th>
						<th align = "center" style = "width:6mm;">Note</th>
					</tr>
					@if(json_decode($spec->spec_table_data))
						@setvar($serial = 1)
						@foreach(json_decode($spec->spec_table_data) as $row)
							<tr valign = "top">
								<td align = "center" style = "width:6mm;">{{ $serial++ }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[0] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[1] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[2] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[3] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[4] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[5] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[6] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[7] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[8] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[9] }}</td>
							</tr>
						@endforeach
					@endif
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
					<caption>Production Settings</caption>
					<tr valign = "top">
						<th align = "center" style = "width:6mm;">Temperature</th>
						<th align = "center" style = "width:6mm;">Dwell time</th>
						<th align = "center" style = "width:6mm;">Pressure</th>
						<th align = "center" style = "width:6mm;">Run Time</th>
						<th align = "center" style = "width:6mm;">Type</th>
						<th align = "center" style = "width:6mm;">Font</th>
						<th align = "center" style = "width:6mm;">Variation name</th>
						<th align = "center" style = "width:6mm;">Details</th>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">{{ $spec->temperature }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->dwell_time }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->pressure }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->run_time }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->type }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->font }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->variation_name }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->details }}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
					<caption>Special notes</caption>
					<tr valign = "top">
						<th align = "center" style = "width:6mm;">Special Notes for Product Options</th>
						<th align = "center" style = "width:6mm;">Option Name</th>
						<th align = "center" style = "width:6mm;">Details</th>
					</tr>
					@if(json_decode($spec->special_note))
						@foreach(json_decode($spec->special_note) as $row)
							<tr valign = "top">
								<td align = "center" style = "width:6mm;">{{ $row[0] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[1] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[2] }}</td>
							</tr>
						@endforeach
					@endif
				</table>
			</td>
		</tr>
		{{-- Financial info --}}
		<tr>
			<td></td>
		</tr>
		<tr>
			<td>
				<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
					<tr valign = "top">
						<th align = "center" style = "width:6mm;">Qty, pcs</th>
						<th align = "center" style = "width:6mm;">1</th>
						<th align = "center" style = "width:6mm;">10</th>
						<th align = "center" style = "width:6mm;">100</th>
						<th align = "center" style = "width:6mm;">1000</th>
						<th align = "center" style = "width:6mm;">10000</th>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">Cost, $</td>
						<td align = "center" style = "width:6mm;">{{ $spec->cost_of_1 }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->cost_of_10 }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->cost_of_100 }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->cost_of_1000 }}</td>
						<td align = "center" style = "width:6mm;">{{ $spec->cost_of_10000 }}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table style = "width:210mm;" cellpadding = "2" cellspacing = "2" border = "0">
					<caption>Product Content Info (Current Cost)</caption>
					<tr valign = "top">
						<th align = "center" style = "width:6mm;">Parts Name</th>
						<th align = "center" style = "width:6mm;">Cost Variation 1, $</th>
						<th align = "center" style = "width:6mm;">Cost Variation 2, $</th>
						<th align = "center" style = "width:6mm;">Cost Variation 3, $</th>
						<th align = "center" style = "width:6mm;">Cost Variation 4, $</th>
						<th align = "center" style = "width:6mm;">Supplier Name</th>
					</tr>
					@setvar($total_cost_var_1 = 0)
					@setvar($total_cost_var_2 = 0)
					@setvar($total_cost_var_3 = 0)
					@setvar($total_cost_var_4 = 0)
					@if(json_decode($spec->content_cost_info))
						@foreach(json_decode($spec->content_cost_info) as $row)
							@setvar($total_cost_var_1 += intval($row[1]))
							@setvar($total_cost_var_2 += intval($row[2]))
							@setvar($total_cost_var_3 += intval($row[3]))
							@setvar($total_cost_var_4 += intval($row[4]))
							<tr valign = "top">
								<td align = "center" style = "width:6mm;">{{ $row[0] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[1] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[2] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[3] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[4] }}</td>
								<td align = "center" style = "width:6mm;">{{ $row[5] }}</td>
							</tr>
						@endforeach
					@endif
					<tr>
						<td colspan = "6">
							<hr />
						</td>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">Total (SUM), $</td>
						<td align = "center" style = "width:6mm;">{{ $total_cost_var_1 }}</td>
						<td align = "center" style = "width:6mm;">{{ $total_cost_var_2 }}</td>
						<td align = "center" style = "width:6mm;">{{ $total_cost_var_3 }}</td>
						<td align = "center" style = "width:6mm;">{{ $total_cost_var_4 }}</td>
						<td align = "center" style = "width:6mm;"></td>
					</tr>
					<tr>
						<td colspan = "6">
							<hr />
						</td>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">Delivery to us, $</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->delivery_cost_variation)[0] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->delivery_cost_variation)[1] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->delivery_cost_variation)[2] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->delivery_cost_variation)[3] }}</td>
						<td align = "center" style = "width:6mm;"></td>
					</tr>
					<tr>
						<td colspan = "6">
							<hr />
						</td>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">Labor Expense to complete Production Process, $</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->labor_expense_cost_variation)[0] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->labor_expense_cost_variation)[1] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->labor_expense_cost_variation)[2] }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ json_decode($spec->labor_expense_cost_variation)[3] }}</td>
						<td align = "center" style = "width:6mm;"></td>
					</tr>
					<tr>
						<td colspan = "6">
							<hr />
						</td>
					</tr>
					<tr valign = "top">
						<td align = "center" style = "width:6mm;">Total cost variation, $</td>
						<td align = "center"
						    style = "width:6mm;">{{ intval(json_decode($spec->labor_expense_cost_variation)[0]) + intval(json_decode($spec->labor_expense_cost_variation)[0]) + $total_cost_var_1 }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ intval(json_decode($spec->labor_expense_cost_variation)[1]) + intval(json_decode($spec->labor_expense_cost_variation)[1]) + $total_cost_var_2 }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ intval(json_decode($spec->labor_expense_cost_variation)[2]) + intval(json_decode($spec->labor_expense_cost_variation)[2]) + $total_cost_var_3 }}</td>
						<td align = "center"
						    style = "width:6mm;">{{ intval(json_decode($spec->labor_expense_cost_variation)[3]) + intval(json_decode($spec->labor_expense_cost_variation)[3]) + $total_cost_var_4 }}</td>
						<td align = "center" style = "width:6mm;"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
