<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
<style>
	body {
		margin: 10px;
		font-family: "Times New Roman", Times, serif; /*Verdana, Arial, Helvetica, sans-serif; */
		font-size: 14px;
	}

	td {
		font-size: 14px;
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
<body>
	@foreach($modules as $module)
		{!! $module !!}
	@endforeach
</body>
</html>