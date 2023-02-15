<div class = "modal fade {{ $unique }}" tabindex = "-1" role = "dialog">
	<div class = "modal-dialog">
		<div class = "modal-content">
			<div class = "modal-header">
				<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close"><span
							aria-hidden = "true">&times;</span></button>
				<h4 class = "modal-title">Add {{ $sku }}</h4>
			</div>
			<div class = "modal-body">
				<table class = "table table-bordered">
					{!! Form::hidden("unique", $unique, ['class' => 'hidden_unique']) !!}
					@foreach($crawled_data[$id_catalog] as $node)
						<tr>
							@setvar($label = \Monogram\Helper::specialCharsRemover($node['label']))
							@if($node['type'] == 'text')
								<td>{{ $label }}</td>
								<td>{!! Form::text("$label", null, ['class' => 'form-control option-field']) !!}</td>
							@elseif($node['type'] == 'select' && strtolower($label) != 'confirmation of order details')
								<td>{{ $label }}</td>
								<td>
									{!! Form::select("$label", \Monogram\Helper::getOnlyValuesByKey($node['options'], "value"), null, ['class' => 'form-control option-field']) !!}
								</td>
							@endif
						</tr>
					@endforeach
				</table>
			</div>
			<div class = "modal-footer">
				<button type = "button" class = "btn btn-default cancel" data-dismiss = "modal">Cancel</button>
				<button type = "button" class = "btn btn-primary add-item">Add Customization</button>
			</div>
		</div>
	</div>
</div>