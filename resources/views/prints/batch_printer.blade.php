<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>
		@if (isset($title))
			{{ $title }} Summaries
		@else
			Batch Summary
		@endif
	</title>
	<style type = "text/css">
		body,td,th {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 10px;
			color: #000000; /*#686869; #2A7D97;*/
			margin-top: 0px;
		}
		.nobreak tr {
			page-break-inside: avoid;
		}
		hr {
			width:100%;
    	color: black;
	    padding-top: 1px;
		}
		
		#background{
		    position:absolute;
		    z-index:0;
		    min-height:50%; 
		    min-width:50%;
		}

		#content{
		    z-index:1;
		}

		#bg-text
		{
		    color:#E0E0E0;
				opacity: 0.5;
		    font-size:120px;
		    transform:rotate(300deg);
		    -webkit-transform:rotate(300deg);
		}
		@page {
			width: 210mm;
			height: 297mm;
			margin-top: 10mm;
		}
		@media print {
			div.current-batch {
				page-break-before: always;
			}
		}
	</style>
	<script>
		window.print();
		@if (count($modules) == 1)
    	setTimeout(window.close, 0);
		@endif
	</script>
</head>
<body>
	@foreach($modules as $module)
		{!! $module !!}
	@endforeach
</body>
</html>
