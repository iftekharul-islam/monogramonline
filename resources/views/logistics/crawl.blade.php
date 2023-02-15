<html>
<head>
</head>
<body>
	<script src = "https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script>
		$(document).ready(function ()
		{
			jQuery.ajaxSetup({async: false});
			var id = '{{ $id_catalog }}';
			var store_name = '{{ $store_name }}';
			//for (var l = 0; l < ids.length; l++) {
			var dt = [];
			
			 var url = "http://www.monogramonline.com/" + id + ".html";
			// Check
			// if (!store_name) {
			//     var url = "http://www.monogramonline.com/" + id + ".html";
			// } else if (store_name == 'MAH') {
			// 	var url = "http://shop.monogramathome.com/" + id + ".html";
			// }else{
			// 	var url = "http://www."+store_name+"/" + id + ".html";
			// }

			$.get("{{ url(sprintf("/get_file_contents")) }}", {url: url}, function (response)
			{
				var data = $(response).find('div.itemOptionWrap');
				var options = data.find(".multiLineOption");
				var json = [];
				options.each(function (i)
				{
					var label = $(this).find('.itemoption');
					var ele = {};
					label.find("span").remove();
					if ( label.text().indexOf("Offer") > -1 ) {
						//console.log("offer bad");
						return false;
					}
					if ( label.text().indexOf("Monogram:") > -1 ) {
						var note = $(this).find('.monogramComment');
						note.find("a").remove();
						var countInput = 0;
						$(this).find("input").each(function (k)
						{
							countInput++;
						});
						ele.type = "text";
						ele.max = countInput;
						ele.label = label.text();
						ele.short = i;
						ele.note = note.text();
						json.push(ele);
					} else if ( label.text().indexOf("Confirmation") > -1 ) {
						ele.type = "select";
						ele.label = label.text();
						var op = [];
						$(this).find("select option").each(function ()
						{
							var o = {};
							o.text = $(this).text();
							o.value = $(this).attr("value");
							o.price = 0;
							op.push(o);
						});
						ele.options = op;
						ele.short = i;
						var note = label.next();
						note.find("a").remove();
						ele.note = note.text();
						json.push(ele);
					} else {
						var ctrl = $(this).find(":input");
						if ( ctrl.prop('type') == 'select-one' ) {
							ele.type = "select";
							ele.label = label.text();
							var op = [];
							$(this).find("select option").each(function ()
							{
								var tr = $(this).text().split("(");
								var opt = "";
								var opp = 0;
								if ( tr.length > 1 ) {
									opt = tr[0];
									opp = tr[1].slice(1, -1);
								} else {
									opt = $(this).text();
								}
								var o = {};
								o.text = opt;
								o.value = opt;
								o.price = opp;
								op.push(o);
							});
							ele.options = op;
							ele.short = i;
							ele.note = "";
							json.push(ele);
						} else if ( ctrl.prop('type') == 'text' ) {
							ele.type = "text";
							ele.max = 15; //Todo:check in
							ele.label = label.text();
							ele.short = i;
							ele.note = "";
							json.push(ele);
						}
					}
				});
				var js = {};
				var price = getPrice(response);
				js[id] = json;
				js["price"] = price;
				dt.push(js);
				document.write(JSON.stringify(js));
			});

			//}
		});

		function getPrice (response)
		{
			var regex = /[+-]?\d+(\.\d+)?/g;
			var nodeValue = $(response).find("#cycitemprice").text();
			return nodeValue.match(regex).reduce(calculator);
		}

		function calculator (total, price)
		{
			return total + price;
		}
	</script>
</body>
</html>
