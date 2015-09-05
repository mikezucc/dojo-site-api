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
		
		$querystring = "update nodes set views = views + 1 where (nodehash='$nodehash');";
		$result = mysqli_query($link, $querystring);
		
		$hash = $username . $nodehash;
		
		$querystring = "select * from nodes where (nodehash='$nodehash') limit 1;";
		$result = mysqli_query($link, $querystring);
		$nodeSpeck = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$nodeSpeck[] = $queryresponse;	
		}
		$aNode['nodespeck'] = $nodeSpeck;
		
		$noderName = $nodeSpeck[0]['username'];
		$querystring = "select * from users where (username='$noderName');";
		$result = mysqli_query($link, $querystring);
		$userSpeck = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$userSpeck[] = $queryresponse;	
		}
		$aNode['userspeck'] = $userSpeck;
		
		$querystring = "select * from seshboard where (nodehash='$nodehash');";
		$result = mysqli_query($link, $querystring);
		$commentCount = 0;
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$commentCount++;
		}
		$aNode['commentCount'] = $commentCount;
		
		$hashVote = $username . $nodehash;
		
		$querystring = "select * from nojovote where (hash='$hashVote');";
		$result = mysqli_query($link, $querystring);
		$voteBlock = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$voteBlock[] = $queryresponse;
		}
		$aNode['voteBlock'] = $voteBlock;
		
		$querystring = "select * from voted420 where (hash='$hash');";
		$result = mysqli_query($link, $querystring);
		$userblox = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$userblox[] = $queryresponse;	
		}
		$aNode['votedinfo'] = $userblox;
		
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
			$interParentArr = array();
			array_push($interParentArr, $aNode);
			array_push($interParentArr, $interArr);
			array_push($payLoadArr, $interParentArr);
		}
	}
	
	$querystring = "select * from ones where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$onesInfo = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$onesInfo[] = $row;
	}
	$onesUsername = $onesInfo[0]['username'];
	
	$querystring = "select * from users where (username='$onesUsername');";
	$result = mysqli_query($link, $querystring);
	$creatorInfo = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$creatorInfo[] = $row;
	}
	
	$querystring = "select * from roster2 where (username='$username' and dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$followInfo = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$followInfo[] = $row;
	}
	
	$finalPay = array();
	
	array_push($finalPay, $payLoadArr);
	array_push($finalPay, $creatorInfo);
	array_push($finalPay, $followInfo);

	echo(json_encode($finalPay));
	mysqli_close($link);	
?>