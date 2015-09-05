<?php
		error_reporting(0);
	//connect to sql
	$link = mysqli_connect("address", "user", "pass", "dbname");

	//get the input from the url response from clientside and convert it to RAW json
	$url_body = file_get_contents('php://input');
	$json_obj = json_decode($url_body, true); 
	//recover dojo data
	$username = $json_obj['username'];
	$username = mysqli_real_escape_string($link, $username);
	
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

	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);

	$querystring = "delete from posts where posthash='$posthash';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from voted where hash like '%$posthash%';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from commentest where posthash='$posthash';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from noti2 where (target='$posthash' or subject='$posthash');";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from commentboard where posthash='$posthash';";
	$result = mysqli_query($link, $querystring);
	
?>
