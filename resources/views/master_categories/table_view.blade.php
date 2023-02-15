<table class = "table table-bordered">
	<tr>
		<th>#</th>
		<th>category code</th>
		<th>category description</th>
		<th>category display order</th>
		<th>Action</th>
	</tr>
	@foreach($master_categories as $category)
		<tr data-id = "{{$category->id}}">
			<td>{{ $count++ }}</td>
			<td>
				<input class = "form-control" name = "category_code" type = "text"
				       value = "{{$category->master_category_code}}">
			</td>
			<td>
				<input class = "form-control" name = "category_description" type = "text"
				       value = "{{$category->master_category_description}}">
			</td>
			<td>
				<input class = "form-control" name = "category_display_order" type = "text"
				       value = "{{$category->master_category_display_order}}">
			</td>
			<td>
				{{--<a href = "{{ url(sprintf("/products_config/categories/%d", $category->id)) }}" class = "btn btn-success">View</a> | --}}
				<a href = "#" class = "update" data-toggle = "tooltip" data-placement = "top"
				   title = "Edit this item"><i class = "fa fa-pencil-square-o text-success"></i> </a>
				| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
				     title = "Delete this item"><i class = "fa fa-times text-danger"></i> </a>
			</td>
		</tr>
	@endforeach
</table>