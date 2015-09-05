<?php
	error_reporting(0);

	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$dojohash = $json_obj['dojohash'];
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$newname = $json_obj['newname'];
	$newname = mysqli_real_escape_string($link,$newname);
	$username = $json_obj['username'];
	$username = mysqli_real_escape_string($link,$username);
	
	if (empty($dojohash))
	{
		echo("damn yee and your web crawlings!");
		mysqli_close($link);
		return;
	}
	
	$token = $json_obj['token']; 
	$token = mysqli_real_escape_string($link,$token);
	$querystring = "select * from token where (username='$username' and token='$token');";
	$result = mysqli_query($link, $querystring);
	$tokenInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$tokenInfo[] = $queryresponse;	
	}
	
	if (empty($tokenInfo[0]))
	{
		return;
	}

	$querystring = "select * from ones where (username='$username' and dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$list = array();
	while ($unique = mysqli_fetch_assoc($result)) 
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
		$querystring = "update dojos set dojo='$newname' where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);

		echo(json_encode($result));
	}
		mysqli_close($link);
?>
