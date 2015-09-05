<?php
	error_reporting(0);

	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$dojohash = $json_obj['dojohash'];
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$newcode = $json_obj['newcode'];
	$newcode = mysqli_real_escape_string($link,$newcode);
	$email = $json_obj['email'];
	$email = mysqli_real_escape_string($link,$email);
	
	if (empty($dojohash))
	{
		echo("damn yee and your web crawlings!");
		mysqli_close($link);
		return;
	}
	
	$querystring = "select * from ones where (email='$email' and dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$list = array();
	while ($unique = mysqli_fetch_array($result)) 
	{
		$list[] = $unique; 
	}
	
	if (empty($list[0])) 
	{
		//not creator
		echo(json_encode("nope"));
	}
	else
	{
		//creator
		//changed the "dojos" table eventually -- changed here to USERS -- fine. 
		$querystring = "update dojos set code='$newcode' where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);

		echo(json_encode($result));
	}
		mysqli_close($link);
?>
