@if(isset($success) && count($success) > 0)
	<div class = "alert alert-success">
			@foreach($success as $msg)
				<div>
					<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
					<span class="sr-only">success:</span>
					{!! $msg !!}
				</div>
			@endforeach
	</div>
@endif

@if(session('success'))
	<div class = "alert alert-success">
			@foreach(is_array(session('success')) ? session('success') : (array) session('success') as $success)
				<div>
					<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
					<span class="sr-only">success:</span>
					{!! $success !!}
				</div>
			@endforeach
	</div>
@endif