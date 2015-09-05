<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$token = $json_obj['token']; 
	$token = mysqli_real_escape_string($link,$token);
	
	if (empty($username))
	{
		echo("damn yee and your web crawlings!");
		mysqli_close($link);
		return;
	}
	
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
	
	$finalPayload = array();
	if ($username === "erlichbachman")
	{
		$querystring = "select * from dojos order by made desc;";
		$result = mysqli_query($link, $querystring);
		$userFollowComplete = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$userFollowComplete[] = $unique;
		}
		array_push($finalPayload, $userFollowComplete);
	}
	else
	{
		$querystring = "SELECT * FROM roster2 WHERE (username='$username') order by made desc;";
		$result = mysqli_query($link, $querystring);
		$userFollowArr = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			$userFollowArr[] = $unique;
		}
	
		for ($m=0;$m<count($userFollowArr);$m++)
		{
			$userFollowComplete = array();
			$dojohash = $userFollowArr[$m]['dojohash'];
			$querystring = "select * from dojos where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$userFollowComplete = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$userFollowComplete[] = $unique;
			}
			array_push($finalPayload, $userFollowComplete);
		}
	}
	
	echo(json_encode($finalPayload));
	mysqli_close($link);
?>
