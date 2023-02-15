<html>
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>Order Confirmation</title>
	<style type = "text/css">
		@page {
			width: 210mm;
			height: 297mm;
			margin-top: 10mm;
		}
		body,td,th {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 10px;
			margin-top: 0px;
		}
		h2 {
		  font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 18px;
			font-weight:bold;
		}
	</style>
</head>
<body>
	<div>
		<table cellpadding = "0" cellspacing = "0" width = "810" border = "0">
			<tr valign = "top">
				<td style = "width:800px;">
					<table cellpadding = "0" cellspacing = "0" width = "100%" border = "0">
						<tr valign = "top">
							<td style = " width:15%; height:1px;"></td>
							<td style = " width:30%; height:1px;"></td>
							<td style = " width:5%; height:1px;"></td>
							<td style = " width:7%; height:1px;"></td>
							<td style = " width:40%; height:1px;"></td>
						</tr>
						<tr valign = "top">
							<td colspan = "5"><strong>
									Order insert Failure in 5p  for order#
									{{$orderid}} </strong>
								<hr size = "1">
							</td>
						</tr>


							<td colspan = "5">
								<br>
							We would like to assure you that we are working diligently to fulfill your orders.<br/>
							If you have any questions <a href="https://www.monogramonline.com/contact-us">please visit our website</a> 
							or send us an email <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>.<br/>
							<br/>
							We appreciate your business<br/>
							<hr size = "1">
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
	</div>
</body>
</html>
