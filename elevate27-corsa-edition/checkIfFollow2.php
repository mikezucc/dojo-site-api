<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$dojohash = $json_obj['dojohash'];
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	
	if (empty($username))
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
	
	$currdate = date('Y-m-d H:i:s');
	
	$querystring = "select status from roster2 where (username='$username' and  dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$arr = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$arr[] = $row; 
	}
	
	$totalPayload = array();
	
	if (empty($arr))
	{
		array_push($totalPayload,"not");
	}
	else
	{
		array_push($totalPayload,"following");
	}
	
	echo(json_encode($totalPayload));
	
	
?>
