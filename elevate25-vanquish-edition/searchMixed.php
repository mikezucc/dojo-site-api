<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$string = $json_obj['string']; 
	$string = mysqli_real_escape_string($link,$string);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$lati = $json_obj['lati'];
	$longi = $json_obj['longi'];
	
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
	
	$querystring = "select * from users where fullname like '%$string%' order by made desc;";
	$result = mysqli_query($link, $querystring);
	$people = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
	  	$people[] = $queryresponse;
	}
	
	$peopleArray = array();
	for ($i=0;$i<count($people);$i++)
	{
		$intermediate = array();
		$friendInfo = array();
		$thatUsersFriends = array();
		$thatUsersMembership = array();
		$row = $people[$i];
		//get info for user 2 (not you)
		$friendemail = $row['username'];
		$querystring = "select * from users where username='$friendemail';";
		$result = mysqli_query($link, $querystring);

		$friendInfo = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$friendInfo[] = $queryresponse;
		}
		
		array_push($intermediate, "person");
		array_push($intermediate, $friendInfo[0]);
		array_push($peopleArray, $intermediate);
	}
	
	$querystring = "select * from dojos where (dojo like '%$string%') order by made desc;";
	$result = mysqli_query($link, $querystring);
	$dojos = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
	  	$dojos[] = $queryresponse;
	}
	
	$dojoArray = array();
	for ($i=0;$i<count($dojos);$i++)
	{
		$intermediate = array();
		$thatDojosMembers = array();
		$thatDojosFriends = array();
		$thatDojosPosts = array(); 
		$dojo = $dojos[$i];
		$dojohash = $dojo['dojohash'];
		
		array_push($intermediate, "dojo");
		array_push($intermediate, $dojo);
		array_push($dojoArray, $intermediate);
	}
	
	//echo(json_encode($dojoArray));
	
	$querystring = "select * from textelems where (textbody like '%$string%') order by updated desc;";
	$result = mysqli_query($link, $querystring);
	$textElems = array();

	while($queryresponse = mysqli_fetch_assoc($result))
	{
	  	$textElems[] = $queryresponse;
	}
	
	//echo(json_encode($textElems));
	
	$duplicateDict = array();
	$nojoArray = array();
	for ($i=0;$i<count($textElems);$i++)
	{
		$elemhash = $textElems[$i]['elemhash'];
		
		$querystring = "select * from elements where elemhash='$elemhash' order by made desc;";
		$result = mysqli_query($link, $querystring);
		$elemListA = array();

		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$elemListA[] = $queryresponse;
		}
		$nodehash = $elemListA[0]['nodehash'];
		
		if (isset($duplicateDict[$nodehash]))
		{
			
		}
		else
		{
			$duplicateDict[$nodehash] = 'y';
		
			$querystring = "select * from nojo where nodehash='$nodehash' order by connected desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoTemp = array();

			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$nojoTemp[] = $queryresponse;
			}	
		
			//echo(json_encode($nojoTemp));
			//echo(json_encode($nodehash));
			
			
			$querystring = "select * from nodes where nodehash='$nodehash' order by made desc;";
			$result = mysqli_query($link, $querystring);
			$nojoInfoSpec = array();

			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$nojoInfoSpec[] = $queryresponse;
			}
		
			$dictObject = array();
		
			$dictObject['nojoInfo'] = $nojoInfoSpec;
		
			$intermediateFrame = array();
			for ($i = 0; $i < count($nojoTemp); $i++)
			{
				$interArr = array();
		
				$aNode = $nojoTemp[$i];
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
					array_push($intermediateFrame, $interParentArr);
				}
			}
			$dictObject['sesh'] = $intermediateFrame;
			array_push($nojoArray, $dictObject);
			
		}
	}
	
	/*
	
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
	
	*/
	
	$listACounter = 0;
	$listBCounter = 0;
	
	$totalLengthOfResults = count($dojoArray) + count($peopleArray);
	$totalPayload = array();
	for ($n = 0; $n < $totalLengthOfResults; $n++)
	{
		$interim = array();
		//echo(json_encode("iterating"));
		if ($listACounter < count($dojoArray))
		{
			if ($listBCounter < count($peopleArray))
			{
				$aDojo = $dojoArray[$listACounter][1];
				$aDojoTime = new DateTime($aDojo['made']);
				
				$aPerson = $peopleArray[$listBCounter][1];
				$aPersonTime = new DateTime($aPerson['made']);
				
				$distance = $aPersonTime->diff($aDojoTime);
				$distanceStr = $distance->format('%R');
				if ($distanceStr == '+')
				{
					array_push($totalPayload, $dojoArray[$listACounter]);
					$listACounter++;
				}
				else
				{
					array_push($totalPayload, $peopleArray[$listBCounter]);
					$listBCounter++;
				}
			}
			else
			{
				array_push($totalPayload, $dojoArray[$listACounter]);
				$listACounter++;
			}
		}
		else
		{
			if ($listBCounter < count($peopleArray))
			{
				array_push($totalPayload, $peopleArray[$listBCounter]);
				$listBCounter++;
			}
		}
	}
	
	$orderedResults = array();
	
	array_push($orderedResults, $nojoArray);
	array_push($orderedResults, $totalPayload);
	
	echo(json_encode($orderedResults));	
	mysqli_close($link);
	
?>
