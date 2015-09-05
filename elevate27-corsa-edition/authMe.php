<?php
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$salt1String = $json_obj['salt1'];
	$salt1Float = floatval($salt1String);
	$fullname = $json_obj['fullname'];
	$fullname = mysqli_real_escape_string($link,$fullname);
	
	$querystring = "select * from users where (fullname='$fullname');";
	$result = mysqli_query($link, $querystring);
	$userInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$userInfo[] = $queryresponse;	
	}
	
	$username = $userInfo[0]['username'];
	
	$querystring = "select * from salts where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$salts = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$salts[] = $queryresponse;	
	}
	
	$totalPayload = array();
	if (!empty($salts[0]))
	{
		$salt3String = $salts[0]['salt1'];
		$salt3Float = floatval($salt3String);
		$password = $salt3Float/$salt1Float;
		array_push($totalPayload, $password);
		$currdate = date('Y-m-d H:i:s');
		$querystring = "insert into lobbyroom (salt1, username, made) values ('$salt3String', '$username', '$currdate');";
		$result = mysqli_query($link, $querystring);
		array_push($totalPayload, $userInfo);
	}
	else
	{
		$nope = "nope";
		array_push($totalPayload, $nope);
	}
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>
