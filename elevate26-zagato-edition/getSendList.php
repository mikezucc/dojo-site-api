<?php
	error_reporting(0);
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
	
	$lati = $json_obj['lati'];
	$longi = $json_obj['longi'];
	
	$differenceLatiHigh = $lati + 1;
	$differenceLatiLow = $lati - 1;
	$differenceLongiHigh = $longi + 1;
	$differenceLongiLow = $longi - 1;
	
	//changed the "dojos" table eventually -- changed here to USERS -- fine. 
	$querystring = "select * from roster2 where (username='$username') order by made desc;";
	$result = mysqli_query($link, $querystring);
	$dojoList = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$dojoList[] = $row; 
	}
	$dojoJoinedList = array();
	$dojoProximityList = array();
	for ($i=0;$i<count($dojoList);$i++)
	{
		$dojoDict = $dojoList[$i];
		$dojoHash = $dojoDict['dojohash'];
		$dojoDataList = array();
		if ($dojoDict['status']==='following')
		{
			$intermediaryArr = array();
			$querystring = "select * from dojos where dojohash='$dojoHash';";
			$result = mysqli_query($link, $querystring);
			while($row = mysqli_fetch_assoc($result)) 
			{
				$dojoDataList[] = $row; 
			}
			array_push($dojoJoinedList, $dojoDataList);
		}
	}
	
	$querystring = "( SELECT * FROM locations WHERE ((lati >= '$differenceLatiLow') and (lati <= '$differenceLatiHigh'))  order by updated desc) UNION (SELECT * FROM locations WHERE ((longi < '$differenceLongiHigh') and (longi > '$differenceLongiLow')) order by updated desc);";
	$result = mysqli_query($link, $querystring);
	$proximityArray = array();
	while ($unique = mysqli_fetch_assoc($result)) {
		//grab the dojos name and set it
		$proximityArray[] = $unique;
	}
	
	for ($i=0;$i<count($proximityArray);$i++)
	{
		$dojoDict = $proximityArray[$i];
		$dojoHash = $dojoDict['dojohash'];
		
		$isFollowing = false;
		for ($n=0;$n<count($dojoJoinedList);$n++)
		{
			$dojoHashCompare = $dojoJoinedList[$n][0]['dojohash'];
			if ($dojoHashCompare === $dojoHash)
			{
				$isFollowing = true;
			}
		}
		if ($isFollowing)
		{
			continue;
		}
		
		$querystring = "select * from roster2 where (dojohash='$dojoHash');";
		$result = mysqli_query($link, $querystring);
		$rosterAmount = array();
		while($row = mysqli_fetch_assoc($result)) 
		{
			$rosterAmount[] = $row; 
		}
		if (!empty($rosterAmount[0]))
		{
			$intermediaryArr = array();
			$querystring = "select * from dojos where dojohash='$dojoHash';";
			$result = mysqli_query($link, $querystring);
			$dojoDataList = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$dojoDataList[] = $row; 
			}
			if (!empty($dojoDataList[0]))
			{
				array_push($dojoProximityList, $dojoDataList);
			}
		}
	}
	$totalPayload = array();
	array_push($totalPayload,$dojoJoinedList);
	array_push($totalPayload,$dojoProximityList);
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>
