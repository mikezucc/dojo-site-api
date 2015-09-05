<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");

	$username = $_GET['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$dojohash = $_GET['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	
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
		return;
	}
	
	$currdate = date('Y-m-d H:i:s');
	$currdateFormat = new DateTime($currdate);
	
	// set all these messages to seen
	$querystring = "select * from messagest where (dojohash='$dojohash' and username='$username');";
	$result = mysqli_query($link, $querystring);
	$commentestArr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$commentestArr[] = $row; 
	}
	
	header('Content-Type: application/javascript');
	
	if (!empty($commentestArr))
	{
		$flagger = $commentestArr[0]['seen'];
		if ($flagger === "no")
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
