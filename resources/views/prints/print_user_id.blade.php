<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>Print a User ID</title>
	<link rel = "stylesheet" type = "text/css" href = "{{url("/assets/css/single_batch_print_css.css")}}" />
	<style type = "text/css">
		@page {
			width: 8.89cm;
			height: 2.54cm;
			margin-top: 1cm;
			margin-left: 0cm;
			margin-right: 0cm;
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 14px;
		}
		@media print {
			div.current-batch {
				page-break-before: always;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 14px;
				/* border-style: solid;
				border-size: 1px;
				border-color: black; */
				overflow: hidden;
			}
		}
		div.current-batch {
				page-break-before: always;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 14px;
				/* border-style: dotted;
				border-size: 0px;
				border-color: black; */
 				overflow: hidden;
		}
	</style>
</head>
<body>
<div style="width: 8.88cm; height: 2.53cm;">

<table cellpadding = "4" cellspacing = "4" width = "100%" border = "0">
	
	<tr>
		<td colspan = "1" align = "center" style="font-size: 12px">
			{!! \Monogram\Helper::getHtmlBarcode(sprintf("%s", $user_code)) !!}
			<br>
			{{ $name }}
		</td>
	</tr>

</table>

<div>
</body>
</html>
