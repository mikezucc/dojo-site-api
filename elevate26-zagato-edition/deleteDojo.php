<?php
		error_reporting(0);
	//connect to sql
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");

	//get the input from the url response from clientside and convert it to RAW json
	$url_body = file_get_contents('php://input');
	$json_obj = json_decode($url_body, true); 
	//recover dojo data
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

	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	
	$querystring = "select * from ones where (username='$username' and dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$list = array();
	while ($unique = mysqli_fetch_assoc($result)) 
	{
		$list[] = $unique; 
	}
	
	if (!empty($list))
	{
		$querystring = "delete from posts where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from messageboard where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from roster2 where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from voted where hash like '%$dojohash%';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from messageboard where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from messagest where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from locations where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		$querystring = "delete from dojos where dojohash='$dojohash';";
		$result = mysqli_query($link, $querystring);
		
	}
	
?>
