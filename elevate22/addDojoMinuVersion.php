<?php
	error_reporting(0);
	//connect to sql
	$link = mysqli_connect("address", "user", "pass", "dbname");
	
	$name = $_GET['name'];
	$name = mysqli_real_escape_string($link,$name);
	if (empty($name))
	{
		echo("damn yee and your web crawlings!");
		mysqli_close($link);
		return;
	}
	
	$dojohash = $_GET['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$longi = $_GET['longi'];
	$lati = $_GET['lati'];

	$currdate = date('Y-m-d H:i:s');

	//create dojo
	//dojos must be CHANGED
	$querystring = "insert into dojos (dojo, dojohash, made) values ('$name', '$dojohash','$currdate');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "insert into locations (dojohash, lati, longi, updated) values ('$dojohash', '$lati', '$longi', '$currdate');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "insert into ones (dojohash, username) values ('$dojohash', 'doji@getdojo.co');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));

	mysqli_close($link);
?>
