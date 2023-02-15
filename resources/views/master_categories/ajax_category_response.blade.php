{{--
<div class = "form-group" data-parent="{{$id}}">
	{!! Form::label('category', 'category', ['class' => 'col-xs-2 control-label']) !!}
	<div class = "col-sm-4">
		{!! Form::select('category', $categories, null, ['id' => '', 'class' => "form-control parent-selector", "size" => 12]) !!}
	</div>
</div>
--}}
<div class = "col-sm-4" data-parent = "{{$id}}" style = "margin-top: 10px;">
	<select name = "category" class = "form-control parent-selector" size = '12'>
		<option value = "{{$id == 0 ? 0 : ""}}" selected>Select a category</option>
		@foreach($categories as $category)
			<option value = "{{$category->id}}" data-display-order = "{{$category->master_category_display_order}}"
			        data-code = "{{$category->master_category_code}}">
				{{--{{$category->master_category_description}}({{\App\Product::where('product_master_category', $category->id)->count()}})--}}
				{{$category->master_category_description}}({{\Monogram\Helper::getProductCount($category->id)}})
			</option>
		@endforeach
	</select>
</div>