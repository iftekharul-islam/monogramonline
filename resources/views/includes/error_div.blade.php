@if($errors->any())
	<div class = "alert alert-danger">
			@foreach($errors->all() as $error)
				<div>
					<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
					<span class="sr-only">Error:</span>
					@if(isset($large) && $large == 1)<strong style="font-size: 120%;">@endif
					{!! $error !!}
					@if(isset($large) && $large == 1)</strong>@endif
				</div>
			@endforeach
	</div>
	
	@if(isset($sound) && $sound == 1)
		<script>
			var audio = new Audio('/assets/sound/ErrorAlert.mp3');
			audio.play();
		</script>	
	@endif	
@endif

@if(session('error'))
	<div class = "alert alert-danger">
			@foreach(is_array(session('error')) ? session('error') : (array) session('error') as $error)
				<div>
					<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
					<span class="sr-only">Error:</span>
					@if(isset($large) && $large == 1)<strong style="font-size: 120%;">@endif
					{!! $error !!}
					@if(isset($large) && $large == 1)</strong>@endif
				</div>
			@endforeach
	</div>
	
	@if(isset($sound) && $sound == 1)
		<script>
			var audio = new Audio('/assets/sound/ErrorAlert.mp3');
			audio.play();
		</script>	
	@endif	
@endif
