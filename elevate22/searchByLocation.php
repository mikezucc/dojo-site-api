<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$dojo = $json_obj['dojo']; 
	$dojo = mysqli_real_escape_string($link,$dojo);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$token = $json_obj['token']; 
	$token = mysqli_real_escape_string($link,$token);
	$lati = $json_obj['lati'];
	$longi = $json_obj['longi'];
	
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
	
	$differenceLatiHigh = $lati + 1;
	$differenceLatiLow = $lati - 1;
	$differenceLongiHigh = $longi + 1;
	$differenceLongiLow = $longi - 1;
	
	$querystring = "SELECT * FROM roster2 WHERE (username='$username') order by made desc;";
	$result = mysqli_query($link, $querystring);
	$userFollowArr = array();
	while ($unique = mysqli_fetch_assoc($result)) {
		$userFollowArr[] = $unique;
	}
	
	$followDistanceArr = array();
	$userFollowComplete = array();
	for ($m=0;$m<count($userFollowArr);$m++)
	{
		$dojohash = $userFollowArr[$m]['dojohash'];
		$querystring = "select * from locations where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		$valid = false;
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$followDistanceArr[] = $unique;
			$valid = true;
		}
		
		if (!$valid)
		{
			$querystring = "select * from locations where (dojohash='swaghash1234');";
			$result = mysqli_query($link, $querystring);
			$valid = false;
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$followDistanceArr[] = $unique;
			}
		}
		
		
		$querystring = "select * from dojos where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$userFollowComplete[] = $unique;
		}
	}
	
	$querystring = "select * from noti2 where (forwhom='$username');";
	$result = mysqli_query($link, $querystring);
	$notificationResults = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notificationResults[] = $row; 
	}
	
	$querystring = "( SELECT * FROM locations WHERE ((lati >= '$differenceLatiLow') and (lati <= '$differenceLatiHigh'))  order by updated desc) UNION (SELECT * FROM locations WHERE ((longi < '$differenceLongiHigh') and (longi > '$differenceLongiLow')) order by updated desc);";
	$result = mysqli_query($link, $querystring);
	$proximityArray = array();
	while ($unique = mysqli_fetch_assoc($result)) {
		//grab the dojos name and set it
		$proximityArray[] = $unique; 
	}
	
	$distanceFar = array();
	$distance100 = array();
	$distance50 = array();
	$distance25 = array();
	$distanceClose = array();
	$followFinal = array();
	for ($i=(count($proximityArray)-1);$i>=0;$i--)
	{
		$aDojo = $proximityArray[$i];
		$dojoHash = $aDojo['dojohash'];
		if (!empty($aDojo))
		{
			$querystring = "SELECT * FROM roster2 WHERE (username='$username' and dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$rosterresults = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$rosterresults[] = $unique;
			}
			//if (!empty($rosterresults[0]))
			//{
			//	continue;
			//}
			/*
			$isFollowing = false;
			for ($j=0;$j<count($userFollowArr);$j++)
			{
				if ($isFollowing == true)
				{
					continue;
				}
				$dojohash1 = $userFollowArr[$j]['dojohash'];
				if ($dojoHash == $dojohash1)
				{
					$isFollowing = true;
				}
			}
			
			if ($isFollowing == true)
			{
				//$dojoHash = $userFollowArr[$foundIdx]['dojohash'];
				//
				continue;
			}
			*/
			
			/*
			$querystring = "SELECT * FROM roster WHERE (dojohash='$dojoHash' and email='$email');";
			$result = mysqli_query($link, $querystring);
			$quickcheck = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$quickcheck[] = $unique;
			}
			if (!empty($quickcheck[0]))
			{
				continue;
			}
			*/
			
			$querystring = "SELECT * FROM roster2 WHERE (dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$rosterresults = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$rosterresults[] = $unique;
			}
			/*
				//old code from search by chunk
				array_push($dojoChunkArr, $numOfSenpai);
				array_push($dojoChunkArr, $dojo);
				array_push($finalArr,$dojoChunkArr);
			*/
			$thatDojosFriends = array();
			for ($k=0;$k<count($rosterresults);$k++)
			{
				$aMember = $rosterresults[$k];
				$aMemberEmail = $aMember['username'];
				$querystring = "select * from sheep where ((leader='$username' and follower='$aMemberEmail') or (follower='$username' and leader='$aMemberEmail')) order by made desc;"; // or (user2='$email' and user1='$aMemberEmail' and status='friends')) order by made desc;";
				$result = mysqli_query($link, $querystring);
				while($queryresponse = mysqli_fetch_assoc($result))
				{
					$thatDojosFriends[] = $queryresponse;
				}
			}
			//change dojos to some sort of dojohash table
			$querystring = "SELECT * FROM dojos WHERE (dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$selectResults = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$selectResults[] = $unique; 
			}
			
			// we are using this dojo
			$querystring = "select message from messageboard where (dojohash='$dojoHash') order by made desc;";
			$result = mysqli_query($link, $querystring);
			$messageTimeArr = array();
			$interval = array();
			$messageTimeArr = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$messageTimeArr[] = $row;
			}
			
			$freshPhotos = array();
			$backgroundPhotos = array();
			$anythingNew = "new";
			
			// set all these messages to seen
			$querystring = "select * from messagest where (dojohash='$dojoHash' and username='$username');";
			$result = mysqli_query($link, $querystring);
			$messageSWAGNIGGA = array();
			while($row = mysqli_fetch_assoc($result)) {
				$messageSWAGNIGGA[] = $row; 
			}
			
			$querystring = "select * from posts where (dojohash='$dojoHash') order by posttime desc;";
			$result = mysqli_query($link, $querystring);
			while($row = mysqli_fetch_assoc($result)) 
			{
				$backgroundPhotos[] = $row; 
			}
								
			$dojoLongi = $aDojo['longi'];
			$dojoLati = $aDojo['lati'];
			$interim = array();
			
			if (!empty($selectResults))
			{
				if ((abs($dojoLongi - $longi) < 0.015) && (abs($dojoLati - $lati) < 0.015))
				{
					$metric = "campus";
					array_push($interim, $aDojo);
					$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
					array_push($interim, $approxDistance);
					array_push($interim, $selectResults[0]);
					array_push($interim, count($rosterresults));
					array_push($interim, $interval);
					array_push($interim, $backgroundPhotos);
					array_push($interim, count($backgroundPhotos));
					array_push($interim, $thatDojosFriends);
					array_push($interim, $rosterresults);
					array_push($interim, $messageSWAGNIGGA);
					array_push($interim, $metric);
					array_push($interim, count($messageTimeArr));
					array_push($distanceClose, $interim);
					continue;
				}
				if ((abs($dojoLongi - $longi) < 0.3) && (abs($dojoLati - $lati) < 0.3))
				{
					$metric = "nearby";
					array_push($interim, $aDojo);
					$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
					array_push($interim, $approxDistance);
					array_push($interim, $selectResults[0]);
					array_push($interim, count($rosterresults));
					array_push($interim, $interval);
					array_push($interim, $backgroundPhotos);
					array_push($interim, count($backgroundPhotos));
					array_push($interim, $thatDojosFriends);
					array_push($interim, $rosterresults);
					array_push($interim, $messageSWAGNIGGA);
					array_push($interim, $metric);
					array_push($interim, count($messageTimeArr));
					array_push($distance25, $interim);
					continue;
				}
				/*
				$metric = "far";
				array_push($interim, $aDojo);
				$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
				array_push($interim, $approxDistance);
				array_push($interim, $selectResults[0]);
				array_push($interim, count($rosterresults));
				array_push($interim, "");
				array_push($interim, $backgroundPhotos);
				array_push($interim, count($backgroundPhotos));
				array_push($interim, $thatDojosFriends);
				array_push($interim, $rosterresults);
				array_push($interim, $messageSWAGNIGGA);
				array_push($interim, $metric);
				array_push($interim, count($messageTimeArr));
				array_push($distanceFar, $interim);
				*/
			}
		}
	}
	
	for($n=0;$n<count($userFollowArr);$n++)
	{
		$dojoHash = $userFollowArr[$n]['dojohash'];
		$querystring = "SELECT * FROM roster2 WHERE (dojohash='$dojoHash' and username!='$username');";
		$result = mysqli_query($link, $querystring);
		$rosterresults = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			$rosterresults[] = $unique;
		}
		/*
			//old code from search by chunk
			array_push($dojoChunkArr, $numOfSenpai);
			array_push($dojoChunkArr, $dojo);
			array_push($finalArr,$dojoChunkArr);
		*/
		$thatDojosFriends = array();
		for ($k=0;$k<count($rosterresults);$k++)
		{
			$aMember = $rosterresults[$k];
			$aMemberEmail = $aMember['username'];
			$querystring = "select * from sheep where (leader='$username' and follower='$aMemberEmail') order by made desc;"; // or (user2='$email' and user1='$aMemberEmail' and status='friends')) order by made desc;";
			$result = mysqli_query($link, $querystring);
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$thatDojosFriends[] = $queryresponse;
			}
		}

		$numOfSenpai = count($rosterresults);
		//change dojos to some sort of dojohash table
			
		// we are using this dojo
		$querystring = "select message from messageboard where (dojohash='$dojoHash') order by made desc;";
		$result = mysqli_query($link, $querystring);
		$interval = array();
		$messageTimeArr = array();
		while($row = mysqli_fetch_assoc($result)) 
		{
			$messageTimeArr[] = $row;
		}
		
		// set all these messages to seen
		$querystring = "select * from messagest where (dojohash='$dojoHash' and username='$username');";
		$result = mysqli_query($link, $querystring);
		$messageSWAGNIGGA = array();
		while($row = mysqli_fetch_assoc($result)) {
			$messageSWAGNIGGA[] = $row; 
		}
		
		$backgroundPhotos = array();
		$querystring = "select * from posts where (dojohash='$dojoHash') order by posttime desc;";
		$result = mysqli_query($link, $querystring);
		while($row = mysqli_fetch_assoc($result)) 
		{
			$backgroundPhotos[] = $row; 
		}
							
		$dojoLongi = $followDistanceArr[$n]['longi'];
		$dojoLati = $followDistanceArr[$n]['lati'];
		$interim = array();
		
		$metric = "far";
		if ((abs($dojoLongi - $longi) < 0.01) && (abs($dojoLati - $lati) < 0.01))
		{
			$metric = "campus";
		}
		else
		{
			if ((abs($dojoLongi - $longi) < 0.08) && (abs($dojoLati - $lati) < 0.08))
			{
				$metric = "nearby";
			}
		}
		
		array_push($interim, $followDistanceArr[$n]);
		$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
		array_push($interim, $approxDistance);
		array_push($interim, $userFollowComplete[$n]);
		array_push($interim, count($rosterresults));
		array_push($interim, "");
		array_push($interim, $backgroundPhotos);
		array_push($interim, count($backgroundPhotos));
		array_push($interim, $thatDojosFriends);
		array_push($interim, $rosterresults);
		array_push($interim, $messageSWAGNIGGA);
		array_push($interim, $metric);
		array_push($interim, count($messageTimeArr));
		array_push($followFinal, $interim);
	}
	
	/*
	if ($isFollowing == true)
	{
		array_push($interim, $aDojo);
		$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
		array_push($interim, $approxDistance);
		array_push($interim, $selectResults[0]);
		array_push($interim, $rosterresults);
		array_push($interim, $interval);
		array_push($interim, $backgroundPhotos);
		array_push($interim, $anythingNew);
		array_push($interim, $thatDojosFriends);
		array_push($interim, $rosterresults);
		array_push($followFinal, $interim);
		continue;
	}
	*/
	
	$finalProximity = array();
	array_push($finalProximity, $followFinal);
	array_push($finalProximity, $distanceClose);
	array_push($finalProximity, $distance25);
	array_push($finalProximity, $distance50);
	array_push($finalProximity, $distance100);
	array_push($finalProximity, $distanceFar);
	echo(json_encode($finalProximity));
	mysqli_close($link);
?>
