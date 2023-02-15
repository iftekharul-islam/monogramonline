<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>Stock# {{ $inventory->stock_no_unique }} Label</title>
	
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
			div.current-label {
				page-break-before: always;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 14px;
				/* border-style: solid;
				border-size: 1px;
				border-color: black; */
				overflow: hidden;
			}
		}
		div.current-label {
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
<div class = "current-label" style="width: 8.88cm; height: 2.53cm;">

<table cellpadding = "0" cellspacing = "0" width = "100%" border = "0">
	<tr valign = "top">
		<td style="font-size: 14px">
			Stock# <b>{{ $inventory->stock_no_unique }}</b>
			Bin# <b>{{ $inventory->wh_bin }}</b>
		</td>
		<td rowspan="2">
			<img style="height:2cm; width: auto; overflow: hidden;" 
												     src = "{{$inventory->warehouse}}"
												     border = "0" />
		</td>		
	</tr>
	<tr valign = "top">
		<td colspan = "1" align = "center" style="font-size: 16px">
			{!! \Monogram\Helper::getHtmlBarcode($inventory->stock_no_unique,2) !!}<br>
			<b>{{ $inventory->stock_name_discription }}</b>
		</td>
	</tr>

</table>

<div>

</body>
</html>
