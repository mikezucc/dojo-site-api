<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
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
		
		$querystring = "select * from sheep where (leader='$friendemail');";
		$result = mysqli_query($link, $querystring);
		$testIf = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$testIf[] = $queryresponse;
		}
		
		$querystring = "select * from roster2 where username='$friendemail' order by made desc;";
		$result = mysqli_query($link, $querystring);
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$thatUsersMembership[] = $queryresponse;
		}
		array_push($intermediate, "person");
		array_push($intermediate, $friendInfo[0]);
		array_push($intermediate, count($testIf));
		array_push($intermediate, count($thatUsersMembership));
		array_push($intermediate, count($testIf));
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
		$querystring = "select * from roster2 where (dojohash='$dojohash') order by made desc;";
		$result = mysqli_query($link, $querystring);
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$thatDojosMembers[] = $queryresponse;
		}
		
		for ($k=0;$k<count($thatDojosMembers);$k++)
		{
			$aMember = $thatDojosMembers[$k];
			$aMemberEmail = $aMember['username'];
			$querystring = "select * from sheep where (leader='$username' and follower='$aMemberEmail') order by made desc;";
			$result = mysqli_query($link, $querystring);
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$thatDojosFriends[] = $queryresponse;
			}
		}
		
		$querystring = "select * from posts where (dojohash='$dojohash') order by posttime desc;";
		$result = mysqli_query($link, $querystring);
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$thatDojosPosts[] = $queryresponse;
		}
		
		array_push($intermediate, "dojo");
		array_push($intermediate, $dojo);
		array_push($intermediate, count($thatDojosMembers));
		array_push($intermediate, count($thatDojosFriends));
		array_push($intermediate, count($thatDojosPosts));
		array_push($dojoArray, $intermediate);
	}
	
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
	
	echo(json_encode($totalPayload));	
	mysqli_close($link);
	
?>
