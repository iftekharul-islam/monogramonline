<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Graphics</title>
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
				<li class = "active">Graphics</li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<ul id="myTab" class="nav nav-tabs">
				<li @if($tab == 'summary') class="active" @endif><a href="/graphics?tab=summary">Summary</a></li>
				<li @if($tab == 'to_export') class="active" @endif><a href="/graphics?tab=to_export">To Export ({!! $count['to_export'] !!})</a></li>
				<li @if($tab == 'exported') class="active" @endif><a href="/graphics?tab=exported">Exported Waiting for Graphics ({!! $count['exported'] !!})</a></li>
				<li @if($tab == 'error') class="active" @endif><a href="/graphics?tab=error">Graphics Error ({!! $count['error'] !!})</a></li>
				<li @if($tab == 'manual') class="active" @endif><a href="/graphics?tab=manual">Manual ({!! $count['manual'] !!})</a></li>
	
			</ul>
			
			<br>
			
			<div id="tabContent" class="tab-content">
				
				<div class="tab-pane fade @if($tab == 'summary') in active @endif" id="summary">
					
					@if ($tab == 'summary')
						@include('graphics.includes.summary')
					@endif
					
				</div>
				
				<div class="tab-pane fade @if($tab == 'to_export') in active @endif" id="to_export">

					@if (isset($to_export) && count($to_export) > 0)  
					  
					  <div class = "col-xs-12 text-center"> 
					  {!! $to_export->render() !!} 
					  </div> 
					  
					  @setvar($type = 'to_export')  
					  @setvar($batches = $to_export)
					  @include('graphics.includes.batch_table')

					@else 
					  <div class = "alert alert-warning">No batches to export</div>
					@endif
					
				</div>
				
				<div class="tab-pane fade @if($tab == 'exported') in active @endif" id="exported">

					@if (isset($exported) && count($exported) > 0)  
					  
					  @setvar($type = 'exported')  
					  @setvar($batches = $exported)
					  @include('graphics.includes.batch_table')
					  
					@else 
					  <div class = "alert alert-warning">No exported batches found</div>
					@endif
					
				</div>
				
				<div class="tab-pane fade @if($tab == 'error') in active @endif" id="error">
					@if (isset($error_list) && count($error_list) > 0) 
					
						@include('graphics.includes.errors')
						
					@endif
				</div>
				
				<div class="tab-pane fade @if($tab == 'manual') in active @endif" id="manual">

					@if ($tab == 'manual' && count($manual) > 0)  

					  @setvar($type = 'manual')  
					  @setvar($batches = $manual)
					  @include('graphics.includes.batch_table')

					@else 
					  <div class = "alert alert-warning">No Manual Batches</div>
					@endif
					
				</div>
				

			
			</div>
			
		</div>

	<script type = "text/javascript">
				
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		
	</script>
</body>
</html>