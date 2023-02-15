<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Preview child SKU</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
				
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
		td {
			width: 1px;
			white-space: nowrap;
		}

		td.description {
			white-space: pre-wrap;
			word-wrap: break-word;
			max-width: 1px;
			width: 100%;
		}

		td textarea {
			border: none;
			width: auto;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Create child sku</li>
		</ol>
		@include('includes.error_div')
		<h3 class = "page-header">Preview child SKU</h3>
		@setvar($i = 0)

		<div class = "col-md-12">
			{!! Form::open(['url' => url('/logistics/post_preview'), 'method' => 'post']) !!}
			{!! Form::hidden('parent_sku', $product->product_model) !!}
			{!! Form::hidden('id_catalog', $id_catalog) !!}
			@setvar($serial = 1)
			<table class = "table table-bordered">
				<thead>
				<tr>
					<th>SL#</th>
					<th></th>
					<th>id</th>
					<th>Parent SKU</th>
					<th>Child SKU</th>
					@foreach($selected_groups as $group)
						@setvar($string =  \Monogram\Helper::htmlFormNameToText($group))
						{!! Form::hidden('selected-group[]', $string) !!}
						<th>{{ $string }}</th>
					@endforeach
				</tr>
				</thead>
				<tbody>
				@foreach($suggestions as $suggestion)
					<tr>
						<td>{{ $serial }}</td>
						<td>{!! Form::checkbox(sprintf("selected-options[]"), json_encode($suggestion['nodes']), true, ['id' => sprintf("selectable-row-%d", $serial), 'class' => 'checkbox selectable']) !!}</td>
						<td>{{ $id_catalog }}</td>
						<td>{{ $product ? $product->product_model : "N/A" }}</td>
						<td>{!! Form::text('selected-child-sku[]', sprintf("%s-%s", $product->product_model, $suggestion['suggestion']), ['class' => 'form-control suggestion', 'style' => 'min-width: 200px;']) !!}</td>
						@foreach($suggestion['nodes'] as $selection)
							<td>{{ $selection }}</td>
						@endforeach
						@setvar(++$serial)
					</tr>
				@endforeach
				</tbody>
				<tfoot>
				<tr>
					<td></td>
					<td id = "select-count"></td>
					<td colspan = "{{ 3 + count($selected_groups) }}" class = "text-right">
						{!! Form::submit('Submit', ['class' => 'btn btn-sm btn-primary']) !!}
					</td>
				</tr>
				</tfoot>
			</table>
			{!! Form::close() !!}
		</div>
	</div>

	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		$(".delete-sku_converter").on('click', function (event)
		{
			event.preventDefault();
			var action = confirm(message.delete);
			if ( action ) {
				$(this).closest('form').submit();
			}
			//return false;
		});

		changeCountView(getCheckboxSelectedCount());

		$("input[type='checkbox'].selectable").on('click', function (event)
		{
			var isChecked = $(this).prop('checked');
			if ( !isChecked ) {
				$(this).closest('tr').find('.suggestion').attr('disabled', true);
			} else {
				$(this).closest('tr').find('.suggestion').attr('disabled', false);
			}
			changeCountView(getCheckboxSelectedCount());
		});

		function getCheckboxSelectedCount ()
		{
			var count = 0;
			$("input[type='checkbox'].selectable").each(function ()
			{
				if ( $(this).prop('checked') ) {
					++count;
				}/* else {
					--count;
				}*/
			});

			return count;
		}

		function changeCountView (count)
		{
			count = count > 0 ? count : 0;

			$("td#select-count").text("Items selected: " + count);
		}
	</script>
</body>
</html>