<?php
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$token = $json_obj['token']; 
	$token = mysqli_real_escape_string($link,$token);
	$lati = 0;//$json_obj['lati'];
	$longi = 0;//$json_obj['longi'];
	
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
	$areaMap = array();
	for ($i=(count($proximityArray)-1);$i>=0;$i--)
	{
		$aDojo = $proximityArray[$i];
		$dojoHash = $aDojo['dojohash'];
		if (!empty($aDojo))
		{
			
			$querystring = "SELECT * FROM roster2 WHERE (dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$rosterresults = 0;
			while ($unique = mysqli_fetch_assoc($result)) {
				$rosterresults++;
			}
			
			$querystring = "SELECT * FROM posts WHERE (dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$postResults = 0;
			$lastPost = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$postResults++;
				$lastPost = $unique;
			}

			$querystring = "SELECT * FROM dojos WHERE (dojohash='$dojoHash');";
			$result = mysqli_query($link, $querystring);
			$selectResults = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$selectResults[] = $unique;
			}
			$dojoName = $selectResults[0]['dojo'];
								
			$dojoLongi = $aDojo['longi'];
			$dojoLati = $aDojo['lati'];
			$interim = array();
			
			if (!empty($selectResults))
			{
				$metric = "map";
				array_push($interim, $aDojo);
				$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
				array_push($interim, $approxDistance);
				array_push($interim, $rosterresults);
				array_push($interim, $metric);
				array_push($interim, $dojoName);
				array_push($interim, $dojoHash);
				array_push($interim, $postResults);
				array_push($interim, $lastPost);
				array_push($areaMap, $interim);
			
				if ((abs($dojoLongi - $longi) < 0.015) && (abs($dojoLati - $lati) < 0.015))
				{
					$metric = "campus";
					array_push($interim, $aDojo);
					$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
					array_push($interim, $approxDistance);
					array_push($interim, $rosterresults);
					array_push($interim, $metric);
					array_push($interim, $dojoName);
					array_push($interim, $dojoHash);
					array_push($interim, $postResults);
					array_push($interim, $lastPost);
					array_push($distanceClose, $interim);
					continue;
				}
				if ((abs($dojoLongi - $longi) < 0.3) && (abs($dojoLati - $lati) < 0.3))
				{
					$metric = "nearby";
					array_push($interim, $aDojo);
					$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
					array_push($interim, $approxDistance);
					array_push($interim, $rosterresults);
					array_push($interim, $metric);
					array_push($interim, $dojoName);
					array_push($interim, $dojoHash);
					array_push($interim, $postResults);
					array_push($interim, $lastPost);
					array_push($distance25, $interim);
					continue;
				}
			}
		}
	}
	
	for($n=0;$n<count($userFollowArr);$n++)
	{
		$dojoHash = $userFollowArr[$n]['dojohash'];
		$querystring = "SELECT * FROM roster2 WHERE (dojohash='$dojoHash');";
		$result = mysqli_query($link, $querystring);
		$rosterresults = 0;
		while ($unique = mysqli_fetch_assoc($result)) {
			$rosterresults++;
		}
		
		$querystring = "SELECT * FROM posts WHERE (dojohash='$dojoHash');";
		$result = mysqli_query($link, $querystring);
		$postResults = 0;
		$lastPost = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			$postResults++;
			$lastPost = $unique;
		}

		$querystring = "SELECT * FROM dojos WHERE (dojohash='$dojoHash');";
		$result = mysqli_query($link, $querystring);
		$selectResults = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$selectResults[] = $unique;
		}
		$dojoName = $selectResults[0]['dojo'];
		
		$querystring = "SELECT * FROM locations WHERE (dojohash='$dojoHash');";
		$result = mysqli_query($link, $querystring);
		$aDoji = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			//grab the dojos name and set it
			$aDoji[] = $unique; 
		}
		
		$dojoLongi = $aDoji[0]['longi'];
		$dojoLati = $aDoji[0]['lati'];
		
		$interim = array();
		
		array_push($interim, $aDoji[0]);
		$approxDistance = sqrt((pow(abs($dojoLongi - $longi),2)) + (pow(abs($dojoLati - $lati),2)));
		array_push($interim, $approxDistance);
		array_push($interim, $rosterresults);
		array_push($interim, $metric);
		array_push($interim, $dojoName);
		array_push($interim, $dojoHash);
		array_push($interim, $postResults);
		array_push($interim, $lastPost);
		array_push($followFinal, $interim);
	}
	
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
