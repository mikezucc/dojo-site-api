<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");

	$username = $_GET['username']; 
	$username = mysqli_real_escape_string($link,$username);
	
	if (empty($username))
	{
		echo("damn yee and your web crawlings!");
		mysqli_close($link);
		return;
	}
	
	$token = $_GET['token'];
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
		$payloadArr = array(
			"flag" => "no",
		);
		echo $_GET['callback'] . "(" . json_encode($payloadArr)  . ")";
	}
	else
	{
		$payloadArr = array(
			"flag" => "yes",
		);
		echo $_GET['callback'] . "(" . json_encode($payloadArr)  . ")";
	}
	mysqli_close($link);
?>
