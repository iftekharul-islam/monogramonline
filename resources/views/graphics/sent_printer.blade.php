<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Sent to Printer</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li><a href = "{{url('/graphics/print_sublimation')}}">Print Sublimation</a></li>
				<li class = "active"><a href = "{{url('/graphics/sent_to_printer')}}">Sent to Printer</a></li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<h3> Sent to Printer</h3>
			
			<div class = "col-xs-12">
				{!! Form::open(['method' => 'get']) !!}
				<div class = "form-group col-xs-3">
					{!! Form::select('printer', $printers, null, ['id' => 'printer_select', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::submit('Filter' , ['class' => 'btn btn-sm btn-default']) !!}
				</div>
			{!! Form::close() !!}
			</div>
			
			@if (isset($summary) && count($summary) > 0) 
			 	<div class="col-md-6 col-xs-12">
					<table class="table table-bordered">
						<thead>
							<th width="200">Sent To:</th>
							<th width="100" style="text-align:right;">0-3 days</th>
							<th width="100" style="text-align:right;">4-7 days</th>
							<th width="100" style="text-align:right;">7+ days</th>
							<th width="100" style="text-align:right;">Total</th>
						</thead>
						<tbody>
							@foreach ($summary as $line)
								<tr>
									<td>
										<a href="{{ url('/graphics/sent_to_printer?printer=' . $line->to_printer) }}">
										{{ $line->to_printer }}
										</a>
									</td>
									<td align="right">
										<a href="{{ url('/graphics/sent_to_printer?printer=' . $line->to_printer . '&date=1') }}">
										{{ $line->group_1 }}
										</a>
									</td>
									<td align="right">
										<a href="{{ url('/graphics/sent_to_printer?printer=' . $line->to_printer . '&date=2') }}">
										{{ $line->group_2 }}
										</a>
									</td>
									<td align="right">
										<a href="{{ url('/graphics/sent_to_printer?printer=' . $line->to_printer . '&date=3') }}">
										{{ $line->group_3 }}
										</a>
									</td>
									<td align="right">
										<a href="{{ url('/graphics/sent_to_printer?printer=' . $line->to_printer) }}">
										{{ $line->batch_count }}
										</a>
									</td>
								</tr>
							@endforeach
							<tr>
								<th>Total:</th>
								<th style="text-align:right;">{{ $summary->sum('group_1') }}</th>
								<th style="text-align:right;">{{ $summary->sum('group_2') }}</th>
								<th style="text-align:right;">{{ $summary->sum('group_3') }}</th>
								<th style="text-align:right;">{{ $summary->sum('batch_count') }}</th>
							</tr>
						</tbody>
					</table>
				</div>
      @elseif (isset($to_printer) && count($to_printer) > 0)  

        <table class="table">
          <thead>
            {!! Form::open(['url' => '', 'id' => 'multiple_form', 'method' => 'get']) !!}
            
            {!! Form::hidden('force', '0') !!}
						{!! Form::hidden('directory', 'sublimation') !!}
            <tr>
							<td colspan=2>
                {!! Form::button('Reprint Selected Batches' , ['id'=>'reprint_multiple', 'class' => 'btn btn-sm btn-info']) !!}
              </td>
              <td colspan=2>
                {!! Form::button('Export Selected Batches Again' , ['id'=>'export6', 'class' => 'btn btn-sm btn-primary']) !!}
              </td>
							<td colspan=6>
								<h4>{{ count($to_printer) }} Batches Found / {{ $total_items }} Item Lines</h4>
							</td>
            </tr>

            <tr>
              <tr>
                <th style="width:30px;">
                  <input type="checkbox" name="select_printer" id="select_printer" class="checkbox">	
                </th>
                <th style="width:150px;">Select All</th>
                <th style="width:300px;">Printed</th>
                <th style="width:150px;">First Order</th>
                <th style="width:50px;">Lines</th>
                <th style="width:250px;">Current Station</th>
                <th style="width:250px;">Date Sent/User</th>
                <th colspan=3></th>
              </tr>
            </tr>
          </thead>

          <tbody>
            
          @foreach($to_printer as $batch) 
            <tr>
              <td>
                <input type = "checkbox" name = "batch_number[]" class = "printer_checkbox"
                       value = "{{ $batch->batch_number }}" />
              </td>
              <td>
                <a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">
                          {{ $batch->batch_number }}</a>
                @if ($batch->status != 'active')
                  ({{ ucfirst($batch->status) }})
                @endif
								<br>
								@if ($batch->store)
									{{ $batch->store->store_name }}
								@endif
								<br>
								{{ $batch->summary_date ? 'Summary Printed' : '' }}
              </td>
              
              <td>
								@if ($batch->to_printer != '1')
									{{ $batch->to_printer }}
									<br>
								@endif
								
								{{ $batch->to_printer_date }}
								
								@if (isset($batch_queue[$batch->batch_number]) && $batch_queue[$batch->batch_number] != '1')
									<br>
									<small>{{  $batch_queue[$batch->batch_number] }}</small>s
								@endif
							</td>
							
              <td>{{ substr($batch->min_order_date, 0 ,10) }}</td>
              <td>
                @if ($batch->itemsCount->first())
                  {{ $batch->itemsCount->first()->count }}
                @endif
              </td>
              <td>
                  <span data-toggle = "tooltip" data-placement = "top"
                      title = "{{ $batch->station_description }}">{{ $batch->station_name }}<br>
                              {{ $batch->station_description }}</span>
              </td>

				<td>
                  <span data-toggle = "tooltip" data-placement = "top"
                      title = "{{ $batch->to_printer_date }}">{{ $batch->to_printer_date }}<br>
                              {{ $scans[$batch->batch_number] }}</span>
              </td>
              
              @if ($batch->first_item)
                                  
                  <td>
                    <span data-toggle = "tooltip" data-placement = "top"
                            title = "{{ $batch->first_item->child_sku }}">
                      <img src = "{{ $batch->first_item->item_thumb }}" width = "70" height = "70" />
                    </span>

                  </td>
                  
              @else
                  
                  <td> No Items </td>
                
              @endif
              
							<td>
								@if ($batch->days > 1)
									Printed {{ $batch->days }} Days Ago
								@endif
							</td>
              <td>
								
                {!! Form::button('Reprint ' . $batch->batch_number, ['class' => 'reprint btn btn-xs btn-info']) !!}

							</td>
            </tr>
          @endforeach
          
            {!! Form::close() !!}
        </tbody>
          
        </table>
        
        {!! Form::open(['url' => 'graphics/reprint_graphic', 'method' => 'post', 'id' => 'reprint_form']) !!}
        {!! Form::hidden('name', null, ['id' => 'reprint_name']) !!}
        {!! Form::close() !!}
        
      @else 
				<div class = "col-xs-12">
        	<div class = "alert alert-warning">No batches sent to printer</div>
				</div>
      @endif

<script>

	var state = false;

	$("#select_printer").on('click', function ()
	{
		state = !state;
		$(".printer_checkbox").prop('checked', state);
	});
	
	$("#export6").click(function() {
			if ( confirm("Are you sure you want to export the selected batches again? \n (All Graphic Status will be lost)") ) {
				$("#multiple_form").attr('action', 'export_batchbulk').submit();
			}
	 });

	 $("#reprint_multiple").click(function() {
 			if ( confirm("Are you sure you want to reprint the selected batches? \n (Graphics will move back to Print Sublimation)") ) {
 				$("#multiple_form").attr('action', 'reprint_bulk').submit();
 			}
 	 });
	 
	 $(".reprint").click(function() {
		 
		 $(this).button('loading');
		 $(this).attr("disabled","disabled"); 
		 
		 var batch = $( this ).text().substr($( this ).text().indexOf(' ') + 1) ;
		 
		 $("#reprint_name").val(batch);
		 
		 $.ajax({
			 type: 'post',
			 url: '{{ url("graphics/reprint_graphic") }}',
			 data: $("#reprint_form").serialize(),
			 context: this,
			 success: function (response) {
				 if (response != 'success') { 
				 	$(this).removeClass('btn-info').addClass('btn-danger');
				} else {
					$(this).removeClass('btn-info').addClass('btn-success');
				}
				 $(this).html(response);
			 } 
		 });
	 });
	 
</script>
			
</body>
</html>