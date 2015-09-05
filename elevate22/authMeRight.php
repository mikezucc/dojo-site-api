<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$salt3String = $json_obj['salt3'];
	$salt3Float = floatval($salt3String);
	$username = $json_obj['username'];
	if (empty($username))
	{	
		return;
	}
	
	$querystring = "select * from lobbyroom where (username='$username' and salt1='$salt3String');";
	$result = mysqli_query($link, $querystring);
	$lobby = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$lobby[] = $queryresponse;	
	}
	
	$querystring = "delete from lobbyroom where (username='$username' and salt1='$salt3String');";
	$result = mysqli_query($link, $querystring);
	
	$currdate = date('Y-m-d H:i:s');
	$totalPayload = array();
	
	if (!empty($lobby[0]))
	{
		$lobbyTime = new DateTime($lobby[0]['made']);
		$nowTime = new DateTime($currdate);
		
		$distance = $nowTime->diff($lobbyTime);
		$distanceStr = $distance->format('%i');
		$distanceNumber = intval($distanceStr);
		if (abs($distanceNumber) < 3)
		{
			// no need to reauth
			//echo("authedalready");
			$querystring = "select * from token where (username='$username');";
			$result = mysqli_query($link, $querystring);
			$tokenInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$tokenInfo[] = $queryresponse;	
			}
			array_push($totalPayload,"success");
			array_push($totalPayload,$tokenInfo);
		}
		else
		{
			// provide token
			$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$length = 20;
			$messagehash = '';
			$count = strlen($charset);
			while ($length--) 
			{
				$messagehash .= $charset[mt_rand(0, $count-1)];
			}
			$querystring = "insert into token (token, username, made) values ('$messagehash', '$username', '$currdate');";
			$result = mysqli_query($link, $querystring);
			$querystring = "select * from token where (username='$username');";
			$result = mysqli_query($link, $querystring);
			$tokenInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$tokenInfo[] = $queryresponse;	
			}
			array_push($totalPayload,"success");
			array_push($totalPayload,$tokenInfo);
		}
	}
	else
	{
		array_push($totalPayload,"nope");
	}
	echo (json_encode($totalPayload));
	mysqli_close($link);
?>
