<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Inventories</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px; margin-left: 10px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('inventoryunit')}}" class = "active">Inventory Unit</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		
		<div class = "col-xs-12">
<div ng-app="myApp" ng-controller="myCtrl"> 


</div>
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
	
	<script>
	var app = angular.module('myApp', []);
	app.controller('myCtrl', function($scope, $http) {
	    $http({
	        method : "GET",
	        url : "/inventoryunit/{child_sku}"
	    }).then(function mySucces(response) {
	        //$scope.myWelcome = response.data;
	    }, function myError(response) {
	        //$scope.myWelcome = response.statusText;
	    });
	});
	</script>

</body>
</html>
