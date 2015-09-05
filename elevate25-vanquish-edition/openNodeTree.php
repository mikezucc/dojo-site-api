<?php 
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$nodehash = $json_obj['nodehash']; 
	$nodehash = mysqli_real_escape_string($link,$nodehash);
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
	
	$payLoadArr = array();
	
	$querystring = "select * from elements where (nodehash='$nodehash') order by made asc;";
	$result = mysqli_query($link, $querystring);
	$elemList = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$elemList[] = $queryresponse;	
	}
	for ($k = 0; $k < count($elemList); $k++)
	{
		$anElem = $elemList[$k];
		$type = $anElem['type'];
		$elemhash = $anElem['elemhash'];
		if ($type === 'text')
		{
			$querystring = "select * from textelems where (elemhash='$elemhash');";
			$result = mysqli_query($link, $querystring);
			$elemText = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$elemText[] = $queryresponse;	
			}
			$anElemt['text'] = $elemText;
		}
		
		$usernameNode = $anElem['username'];
		$querystring = "select * from users where (username='$usernameNode');";
		$result = mysqli_query($link, $querystring);
		$userDat = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$userDat[] = $queryresponse;	
		}
		$anElemt['user'] = $userDat;
		
		array_push($payLoadArr, $anElem);
	}
	
	echo(json_encode($payLoadArr));
	mysqli_close($link);	
?>