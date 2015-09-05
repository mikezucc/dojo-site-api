<?php
	//error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
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
		echo(json_encode("some type of fucking token error"));
		return;
	}
	
	$totalPayload = array();
	
	$querystring = "select * from posts order by posttime desc limit 40;"; 
	$result = mysqli_query($link, $querystring);
	$personPosts = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$personPosts[] = $queryresponse;
	}
	
	for ($k=0;$k<count($personPosts);$k++)
	{
		$intermediate = array();
		$totalUpvote = 0;
		$totalDownvote = 0;
		$posthash = $personPosts[$k]['posthash'];
		$dojohash = $personPosts[$k]['dojohash'];
		$username = $personPosts[$k]['username'];
		
		$dojohash = mysqli_real_escape_string($link,$dojohash);
		//changed the "dojos" table eventually
		
		$querystring = "select * from voted where (hash like '%$posthash%' and vote=1);";
		$result = mysqli_query($link, $querystring);
		$voteUpArr = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$voteUpArr++;
		}
		
		$querystring = "select * from voted where (hash like '%$posthash%' and vote=0);";
		$result = mysqli_query($link, $querystring);
		$voteDownArr = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$voteDownArr++;
		}
		
		$totalUpvote = $voteUpArr;
		$totalDownvote = $voteDownArr;
		
		$querystring = "select * from dojos where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		$dojoInfo = array();
		while($row = mysqli_fetch_assoc($result)) {
			$dojoInfo[] = $row; 
		}
		
		$querystring = "select * from users where (username='$username');";
		$result = mysqli_query($link, $querystring);
		$userInfo = array();
		while($row = mysqli_fetch_assoc($result)) {
			$userInfo[] = $row; 
		}
		
		$querystring = "select * from roster2 where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		$rosterInfo = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$rosterInfo++;
		}
		
		$querystring = "select * from commentboard where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$commentboard = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$commentboard++;
		}
		
		$querystring = "select * from reposts where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$repostInfo = 0;
		while($row = mysqli_fetch_assoc($result)) 
		{
			$repostInfo++;
		}
		
		array_push($intermediate,$personPosts[$k]);
		array_push($intermediate,$totalUpvote);
		array_push($intermediate,$totalDownvote);
		array_push($intermediate,$dojoInfo[0]);
		array_push($intermediate,$dojoInfo);
		array_push($intermediate,$rosterInfo);
		array_push($intermediate,($commentboard - $repostInfo - 1));
		array_push($intermediate,$repostInfo);
		array_push($intermediate,$userInfo[0]);
		array_push($totalPayload,$intermediate);
	}
	
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>