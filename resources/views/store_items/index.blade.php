<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Store Items</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
				
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
  
  <style>
	
  input[type=number] {
    width: 75px;
    text-align: right;
  }
	
	[hidden] {
	  display: none !important;
	}
  </style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 100%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('stores')}}">Manage Stores</a></li>
      @if ($store)
        <li><a href = "{{url('/stores/' .  $store->id . '/edit')}}">{{ $store->store_name }}</a></li>
      @endif
			<li class = "active">Store Items</li>
		</ol>
		
    @include('includes.error_div')
    @include('includes.success_div')
  
		<div class = "col-md-12">
			<h3 class="page-header">
				{{ $store->store_name }} Items <small>({{ count($items) }} found)</small>
			</h3>
			
			<div class="col-md-4">
				<div class = "panel panel-default">
					<div class = "panel-heading">Import / Export</div>
					<div class = "panel-body">
						<div class="col-md-6">
							<a href="{{url('/stores/items/get_csv/' .  $store->store_id)}}" class="btn btn-sm btn-info">Download CSV</a>
						</div>
						{!! Form::open(['url' => url("stores/items/post_csv"), 'method' => 'post', 'files'=>'true']) !!}
						{!! Form::hidden('store_id', $store->store_id) !!}
						<label class="btn btn-sm btn-success">
							<div class="col-md-6">Upload CSV</div>
							<input name="items_csv" type="file" hidden onchange="this.form.submit();">
						</label>
						{!! Form::close() !!}
					</div>
				</div>
		</div>

			<table class = "table table-condensed">
					<thead>
					<tr>
            <th width="10%">Store SKU</th>
            <th width="20%">Description</th>
            <th width="5%">Cost</th>
            <th width="10%">Parent SKU</th>
            <th width="15%">Child SKU</th>
            <th width="20%">URL</th>
            <th width="10%">UPC</th>
            <th width="5%">Custom</th>
            <th colspan=2 width="5%">Action</th>
					</tr>
					</thead>
          
					<tbody>
        
					@if ($store)
						<tr>
							{!! Form::open(['url' => url("stores/items/add")]) !!}
							{!! Form::hidden('store_id', $store->store_id) !!}
							<td>{!! Form::text('vendor_sku', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('description', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::number('cost', '0.00', ['class' => 'form-control', 'min' => '0', 'step' => '.01']) !!}</td>
							<td>{!! Form::text('parent_sku', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('child_sku', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('url', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('upc', '', ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('custom', '', ['class' => 'form-control']) !!}</td>
							<td colspan=2>{!! Form::submit('Add New Item', ['class' => 'btn btn-sm btn-success']) !!}</td>
							{!! Form::close() !!}
						</tr>
					@endif
						
					@foreach($items as $item)
            <tr>
              {!! Form::open(['url' => 'stores/items/update', 'method' => 'post', 'id' => 'form-' . $item->id]) !!}
              {!! Form::hidden('store_id', $item->store_id) !!}
              {!! Form::hidden('id',  $item->id) !!}
              <td>{!! Form::text('vendor_sku', $item->vendor_sku, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::text('description', $item->description, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::number('cost', $item->cost, ['class' => 'form-control', 'min' => '0', 'step' => '.01']) !!}</td>
              <td>{!! Form::text('parent_sku', $item->parent_sku, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::text('child_sku', $item->child_sku, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::text('url', $item->url, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::text('upc', $item->upc, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::text('custom', $item->custom, ['class' => 'form-control']) !!}</td>
              <td>{!! Form::button('Update', ['class' => 'btn btn-sm btn-info update_item', 'id' =>  $item->id]) !!}</td>
              {!! Form::close() !!}
							<td><a href="/stores/items/delete/{{ $item->id }}" class ='btn btn-sm btn-danger'
										onclick = "return confirm('Are you sure you want to delete?');">Delete</a></td>
            </tr>
					@endforeach
          
				</tbody>
			</table>
		</div>

	<script type = "text/javascript">
    
    $(".update_item").click(function() {
      
      $(this).button('loading');
      $(this).attr("disabled","disabled"); 
      
			var form_id = $(this).attr('id');

      $.ajax({
        type: 'post',
        url: '{{ url("stores/items/update") }}',
        data: $("#form-" + form_id).serialize(),
        context: this,
        success: function (response) {
          if (response != 'updated') { 
           $(this).removeClass('btn-info').addClass('btn-danger');
         } else {
           $(this).removeClass('btn-info').addClass('btn-success');
					 $(this).prop("disabled",false); 
         }
          $(this).html(response);
        } 
      });
    });
    
  </script>

	</div>
</body>
</html>