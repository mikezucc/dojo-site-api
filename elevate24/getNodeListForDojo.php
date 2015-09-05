<?php 
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
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
	
	$querystring = "select * from nojo where (dojohash='$dojohash') order by connected desc;";
	$result = mysqli_query($link, $querystring);
	$nojoList = array();
	while($row = mysqli_fetch_assoc($result)) {
		//perform check here for presence of text post to dramatically improve look up time and table lock delay
		$nojoList[] = $row; 
	}
	
	$payLoadArr = array();
	
	for ($i = 0; $i < count($nojoList); $i++)
	{
		$interArr = array();
		
		$aNode = $nojoList[$i];
		$nodehash = $aNode['nodehash'];
		
		$querystring = "select * from elements where (nodehash='$nodehash') order by made asc;";
		$result = mysqli_query($link, $querystring);
		$elemList = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$elemList[] = $queryresponse;	
		}
		if (count($elemList) > 0)
		{
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
					$anElem['text'] = $elemText;
				}
				$posterhash = $anElem['username'];
				$querystring = "select * from users where (username='$posterhash');";
				$result = mysqli_query($link, $querystring);
				$userblox = array();
				while($queryresponse = mysqli_fetch_assoc($result))
				{
					$userblox[] = $queryresponse;	
				}
				$anElem['userinfo'] = $userblox;
				array_push($interArr, $anElem);
			}
			array_push($payLoadArr, $interArr);
		}
	}
	
	echo(json_encode($payLoadArr));
	mysqli_close($link);	
?>