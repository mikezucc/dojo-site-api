<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username'];
	$username = mysqli_real_escape_string($link,$username);
	$profilehash = $json_obj['hash'];
	$profilehash = mysqli_real_escape_string($link,$profilehash);
	
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
		echo("damn yee and your web crawlings!");
		return;
	}
	
	$querystring = "update users set profilehash='$profilehash' where (username='$username');";
	$result = mysqli_query($link, $querystring);
	echo($result);
	mysqli_close($link);
	
?>
