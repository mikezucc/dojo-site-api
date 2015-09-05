<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$username = $_GET['username'];
	$token = $_GET['token'];

	$username = mysqli_real_escape_string($link,$username);
	$token = mysqli_real_escape_string($link,$token);

	$querystring = "select * from token where (username='$username' and token='$token');";
	$result = mysqli_query($link, $querystring);
	$tokenInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$tokenInfo[] = $queryresponse;	
	}
	if (!empty($tokenInfo))
	{
		echo("success");
	}
	else
	{
		echo("fail");
	}

?>
