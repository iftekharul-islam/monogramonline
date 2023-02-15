<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Manage Stores</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
	</style>
</head>

<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('stores')}}">Manage Stores</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class="pull-right">
			{!! Form::open(['url' => 'stores/create', 'method' => 'get', 'id' => 'create-form']) !!}
			{!! Form::submit('Create New Store', ['class' => 'btn btn-success btn-sm']) !!}
			{!! Form::close() !!}
		</div>
		
		@if(!empty($stores) && count($stores) > 0)

			<h3>Stores</h3>
			<br>
			
			<table class="table table-striped table-hover">
				<tbody>
				
				@setvar($total = count($stores))
				@setvar($count = 1)
				
				@foreach($stores as $store)
							<tr>
								<td width="300">
									{!! Form::open(['url' => 'stores/' . $store->id . '/edit', 'method' => 'get']) !!}
									{!! Form::submit($store->store_name, ['class' => 'btn btn-link btn-xs']) !!}
									{!! Form::close() !!}
								</td>
								<td width="300">ID: {{ $store->store_id }}</td>
								<td width="300">Company: {{ $companies[$store->company] }}</td>
								<td width="100">
									@if($count > 1)
										<a href = "{{ url(sprintf('/stores/sort/up/%s', $store->id)) }}"
	 											data-toggle = "tooltip" data-placement = "top"
												title = "Move Up"><i class = 'glyphicon glyphicon-chevron-up'></i></a>
									@endif
								</td>
								<td width="100">
									@if($count < $total)
										<a href = "{{ url(sprintf('/stores/sort/down/%s', $store->id)) }}"
												data-toggle = "tooltip" data-placement = "top"
												title = "Move Down"><i class = 'glyphicon glyphicon-chevron-down'></i></a>
									@endif
								</td>
								<td width="100">
									<a href = "{{ url(sprintf('/stores/visible/%s', $store->id)) }}"
											data-toggle = "tooltip" data-placement = "top"
									@if($store->invisible == '0')
										title = "Hide Store"><i class = 'glyphicon glyphicon-eye-open'></i>
									@else
										title = "Show Store"><i class = 'glyphicon glyphicon-eye-close text-danger'></i>
									@endif
									</a>
								</td>
								<td width="80">
									<a href = "{{ url(sprintf('/stores/items/%s', $store->store_id)) }}"
											data-toggle = "tooltip" data-placement = "top"
											title = "Map Store Items"><i class = 'glyphicon glyphicon-th-list'></i>
									</a>
									@if (count($store->store_items) > 0)
										<small class="text-primary">{{ count($store->store_items) }}</small>
									@endif
								</td>
								<td><a href="{{ route('store-permission', $store->id) }}"><i class = 'glyphicon glyphicon-cog'></i></a></td>
							</tr>
						@setvar($count++)
				@endforeach
				
				</tbody>
			</table>
			
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					No Stores found.
				</div>
			</div>
		@endif
	</div>

</body>
</html>