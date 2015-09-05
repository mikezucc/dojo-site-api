<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$token = $json_obj['token']; 
	$token = mysqli_real_escape_string($link,$token);
	
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
	
	$finalPayload = array();
$querystring = "SELECT * FROM roster2 WHERE (username='$username') order by made desc;";
		$result = mysqli_query($link, $querystring);
		$userFollowArr = array();
		while ($unique = mysqli_fetch_assoc($result)) {
			$userFollowArr[] = $unique;
		}
	
		for ($m=0;$m<count($userFollowArr);$m++)
		{
			$userFollowComplete = array();
			$dojohash = $userFollowArr[$m]['dojohash'];
			$querystring = "select * from dojos where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$userFollowComplete = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$userFollowComplete[] = $unique;
			}
			
			$querystring = "select * from locations where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$locDat = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$locDat[] = $queryresponse;	
			}
			$userFollowComplete[0]['locationData'] = $locDat;
			
			$querystring = "select * from nojo where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$nojoDat = 0;
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$nojoDat++;	
			}
			$userFollowComplete[0]['nojoDat'] = $nojoDat;
			
			$querystring = "select * from roster2 where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$rosterDat = 0;
			$rosterRawArr = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$rosterDat++;
				$rosterRawArr[] = $queryresponse;
			}
			$userFollowComplete[0]['rosterDat'] = $rosterDat;
			
			$rosterArrProcess = array();
			for ($k = 0; $k < $rosterDat; $k++)
			{
				$userhash = $rosterRawArr[$k]['username'];
				
				$querystring = "select * from users where (username='$userhash');";
				$result = mysqli_query($link, $querystring);
				$rosterArr = array();
				while($queryresponse = mysqli_fetch_assoc($result))
				{
					$rosterArr[] = $queryresponse;
				}
				array_push($rosterArrProcess, $rosterArr[0]);
			}
			$userFollowComplete[0]['rosterArr'] = $rosterArrProcess;
			
			$querystring = "select * from slackFlaggy where (dojohash='$dojohash' and username='$username');";
			$result = mysqli_query($link, $querystring);
			$slackFlagDat = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$slackFlagDat[] = $queryresponse;	
			}
			$userFollowComplete[0]['slackFlagDat'] = $slackFlagDat;
			
			array_push($finalPayload, $userFollowComplete);
		}
	
	echo(json_encode($finalPayload));
	mysqli_close($link);
?>
