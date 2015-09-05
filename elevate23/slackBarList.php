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
		$userFollowComplete = array();
		$querystring = "select * from dojos order by made desc;";
		$result = mysqli_query($link, $querystring);
		$userFollowComplete = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$userFollowComplete[] = $unique;
		}
		
		for ($n = 0; $n < count($userFollowComplete); $n++)
		{
			$specHash = $userFollowComplete[$n]['dojohash'];
			$querystring = "select * from roster2 where (dojohash='$specHash' and username='$username');";
			$result = mysqli_query($link, $querystring);
			$isRoster = 0;
			while($row = mysqli_fetch_assoc($result))
			{
				$isRoster = 1;
			}

			if ($isRoster == 0)
			{
				$userFollowComplete[$n]['following'] = "yes";
			}
			else
			{
				$userFollowComplete[$n]['following'] = "no";
			}
		
			array_push($finalPayload, $userFollowComplete[$n]);
		}
	}
	
	echo(json_encode($finalPayload));
	mysqli_close($link);
?>
