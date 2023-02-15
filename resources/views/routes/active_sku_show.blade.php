<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Active SKU</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/css/bootstrap-select.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		td {
			/*width: 1px;*/
			white-space: nowrap;
		}

		td.description {
			white-space: pre-wrap;
			word-wrap: break-word;
			max-width: 300px;
			min-width: 250px !important;
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
			<li><a href = "{{url('/items/active_batch_group')}}">Active batch by SKU group</a></li>
			<li class = "active">Items on station</li>
		</ol>
		@include('includes.error_div')
		<div class = "col-xs-12">
			@if($items)
				{!! Form::open(['method'=>'post', 'url' => url('/items/sku_station_done_reject'), 'id' => 'action_changer']) !!}
				{!! Form::hidden('action', null) !!}
				{!! Form::hidden('sku', $sku) !!}
				{{--{!! Form::hidden('station_name', $station_name) !!}--}}
				{!! Form::close() !!}
				<div class = "col-xs-12" style = "margin-top: 20px;">
					<table class = "table table-bordered" id = "batch-items-table">
						<thead>
						<tr>
							<th>
								<button type = "button" class = "btn btn-danger" id = "reject-all">Reject all</button>
							</th>
							<th>SL#</th>
							<th>Batch#</th>
							<th>Station</th>
							<th>
								Order
								<br />
								Item barcode
							</th>
							<th>Image</th>
							<th>Date</th>
							<th>Qty.</th>
							<th>SKU</th>
							<th>Item name</th>
							<th>Options</th>
							<th>
								<button type = "button" class = "btn btn-success" id = "done-all">Done all</button>
							</th>
						</tr>
						</thead>
						<tbody>
						@foreach($items as $item)
							<tr data-id = "{{$item->id}}">
								<td><a href = "#" class = "btn btn-danger reject">Reject</a></td>
								<td>{{$count++}}</td>
								<td>{{ $item->batch_number }}</td>
								<td>{{ $item->station_details ? $item->station_details->station_description : "-" }}</td>
								{{--<td>{!! \Monogram\Helper::getHtmlBarcode(sprintf("%s-%s", $item->order->short_order, $item->id)) !!}</td>--}}
								<td>
									<a href = "{{url(sprintf('/orders/details/%s', $item->order->order_5p))}}"
									   target = "_blank">{{ $item->order->short_order }}</a> - {{$item->id}}
									<br />
									{!! \Monogram\Helper::getHtmlBarcode(sprintf("%s-%s", $item->order->short_order, $item->id)) !!}
								</td>
								{{--<td>
									<a href = "{{url('/orders/details/'.$item->order->id)}}">{{$item->order->short_order}}</a>
								</td>
								<td>{!! \Monogram\Helper::getHtmlBarcode(sprintf("%s", $item->order->short_order)) !!}</td>--}}
								<td><a href = "{{ $item->product->product_url }}" target = "_blank"><img
												src = "{{$item->item_thumb}}" /></a>
								</td>
								<td>{{substr($item->order->order_date, 0, 10)}}</td>
								<td>{{$item->item_quantity}}</td>
								<td>{{$item->item_code}}</td>
								{{--<td>{{$item->item_description}}</td>--}}
								<td class = "description">{{$item->item_description}}</td>
								<td>{!! Form::textarea('nothing', \Monogram\Helper::jsonTransformer($item->item_option), ['rows' => '4', 'cols' => '20', /*"style" => "border: none; width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"*/]) !!}</td>
								<td>{{--<a href = "#" class = "btn btn-danger reject">Reject</a> |--}} <a href = "#"
								                                                                          class = "btn btn-success done">Done</a>
								</td>
							</tr>
						@endforeach
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
							<td id = "item-quantity-in-total"></td>
							<td></td>
							<td></td>
						</tr>
						</tfoot>
					</table>
				</div>
				{!! Form::open(['url' => url('stations/change'), 'id' => 'station-action', 'method' => 'post']) !!}
				{!! Form::close() !!}
			@endif
		</div>
		<div class = "modal fade" id = "rejection-modal" tabindex = "-1" role = "dialog"
		     aria-labelledby = "myModalLabel">
			<div class = "modal-dialog" role = "document">
				<div class = "modal-content">
					<div class = "modal-header">
						<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close"><span
									aria-hidden = "true">&times;</span></button>
						<h4 class = "modal-title" id = "myModalLabel">Reason to reject?</h4>
					</div>
					<div class = "modal-body">
						<div class = "form-group">
							{!! Form::select('reason_to_reject', $rejection_reasons?: [], null, ['id' => 'reason-to-reject', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group">
							{!! Form::textarea('message_to_reject', null, ['id' => 'message-to-reject', 'rows' => 2, 'class' => 'form-control', 'placeholder' => 'Write the message to reject']) !!}
						</div>
					</div>
					<div class = "modal-footer">
						<button type = "button" class = "btn btn-default" data-dismiss = "modal">Close</button>
						<button type = "button" class = "btn btn-danger" id = "do-reject">Reject</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var form = null;

		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();

			var totalQuantity = 0;
			$("table#batch-items-table tbody tr").each(function ()
			{
				totalQuantity += parseInt($(this).find('td:eq(7)').text());
			});

			$("table#batch-items-table tfoot td#item-quantity-in-total").text("Total quantity: " + totalQuantity);
		});

		$("select#station_change_dropdown").on('change', function ()
		{
			var selected = parseInt($(this).val());
			if ( selected !== 0 ) {
				$(this).closest('form').submit();
			}
		});

		$("a.reject").on('click', function (event)
		{
			event.preventDefault();
			form = $("form#station-action");
			var value = $(this).closest('tr').attr('data-id');
			$("<input type='hidden' value='' />")
					.attr("name", "item_id")
					.attr("value", value)
					.appendTo($("form#station-action"));
			$("<input type='hidden' value='' />")
					.attr("name", "action")
					.attr("value", 'reject')
					.appendTo($("form#station-action"));
			$("<input type='hidden' value='' />")
					.attr("name", "return_to")
					.attr("value", "back")
					.appendTo($("form#station-action"));

			$("#rejection-modal").modal('show');
		});

		$("button#reject-all").on('click', function (event)
		{
			event.preventDefault();
			form = $("form#action_changer");
			var value = 'reject'
			$("input[name='action']").val(value);
			$("#rejection-modal").modal('show');
		});

		$("#do-reject").on('click', function ()
		{
			var rejection_message = $("#message-to-reject").val().trim();
			if ( !rejection_message ) {
				alert('Rejection message cannot be empty!');
				return false;
			}
			var rejection_reason = $("#reason-to-reject").val();
			if ( !rejection_reason || rejection_reason == 0 ) {
				alert('Rejection reason cannot be unselected!');
				return false;
			}
			$("<input type='hidden' value='' />")
					.attr("name", "rejection_reason")
					.attr("value", rejection_reason)
					.appendTo($(form));
			$("<input type='hidden' value='' />")
					.attr("name", "rejection_message")
					.attr("value", rejection_message)
					.appendTo($(form));
			$(form).submit();
		});

		$("a.done").on('click', function (event)
		{
			event.preventDefault();
			var value = $(this).closest('tr').attr('data-id');
			$("<input type='hidden' value='' />")
					.attr("name", "item_id")
					.attr("value", value)
					.appendTo($("form#station-action"));
			$("<input type='hidden' value='' />")
					.attr("name", "action")
					.attr("value", 'done')
					.appendTo($("form#station-action"));
			$("<input type='hidden' value='' />")
					.attr("name", "return_to")
					.attr("value", "back")
					.appendTo($("form#station-action"));
			$("form#station-action").submit();
		});
		$("button#done-all").on('click', function (event)
		{
			event.preventDefault();
			var value = 'done'
			$("input[name='action']").val(value);
			$("form#action_changer").submit();
		})
	</script>
</body>
</html>