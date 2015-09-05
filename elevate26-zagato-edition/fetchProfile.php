<?php 
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

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
	
	$totalPayload = array();
	$otherPersonInfo = array();

	$personemail = $json_obj['person'];
	$personemail = mysqli_real_escape_string($link,$personemail);
	
	$querystring = "select * from users where (username='$personemail');"; 
	$result = mysqli_query($link, $querystring);
	$personInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$personInfo[] = $queryresponse;
	}
	
	$person = $personInfo[0];
	
	$querystring = "select * from nojo where (username='$personemail') order by connected desc;";
	$result = mysqli_query($link, $querystring);
	$nojoList = array();
	while($row = mysqli_fetch_assoc($result)) {
		//perform check here for presence of text post to dramatically improve look up time and table lock delay
		$nojoList[] = $row; 
	}
	
	$postPayloadArr = array();
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
			array_push($postPayloadArr, $interParentArr);
		}
	}
	
	$querystring = "select * from sheep where (leader='$personemail' and status='following');";
	$result = mysqli_query($link, $querystring);
	$followerRaw = array();
	while($row = mysqli_fetch_assoc($result)) {
		$followerRaw[] = $row; 
	}
	
	$hurrumph = count($followerRaw);
	array_push($otherPersonInfo, $hurrumph);
	
	$followerArr = array();
	for ($k=0;$k<count($followerRaw);$k++)
	{
		$intermediate = array();
		$followeremail = $followerRaw[$k]['follower'];
		$querystring = "select * from users where (username='$followeremail');"; 
		$result = mysqli_query($link, $querystring);
		$personInfo2 = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$personInfo2[] = $queryresponse;
		}
		
		$totalPoints = $personInfo2[0]['glory'];
		
		array_push($intermediate,$personInfo2);
		array_push($intermediate,$totalPoints);
		array_push($followerArr,$intermediate);
	}
	
	array_push($totalPayload,$postPayloadArr);
	array_push($totalPayload,$followerArr);
	array_push($totalPayload,$otherPersonInfo); // 0 is points, 1 is follower count
	array_push($totalPayload,$personInfo);
	
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>
