<?php 
	error_reporting(0);
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
	
	$querystring = "select * from posts where (username='$personemail') order by posttime desc;"; 
	$result = mysqli_query($link, $querystring);
	$personPosts = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$personPosts[] = $queryresponse;
	}
	
	$legitbased = array();
	$voteArray = array();
	$posterArray = array();
	$birds = array();
	$totalVotes = 0;

	for ($k=0;$k<count($personPosts);$k++)
	{
		$intermediate = array();
		$totalUpvote = 0;
		$totalDownvote = 0;
		$posthash = $personPosts[$k]['posthash'];
		$dojohash = $personPosts[$k]['dojohash'];
		
		if (in_array($posthash, $birds))
		{
			continue;
		}
		else
		{
			$birds[] = $posthash;
		}
		
		$dojohash = mysqli_real_escape_string($link,$dojohash);
		//changed the "dojos" table eventually
		
		$querystring = "select * from voted where (hash like '%$posthash%' and vote=1);";
		$result = mysqli_query($link, $querystring);
		$voteUpArr = array();
		while($row = mysqli_fetch_assoc($result)) {
			$voteUpArr[] = $row; 
		}
		
		$querystring = "select * from voted where (hash like '%$posthash%' and vote=0);";
		$result = mysqli_query($link, $querystring);
		$voteDownArr = array();
		while($row = mysqli_fetch_assoc($result)) {
			$voteDownArr[] = $row; 
		}
		
		$totalUpvote = count($voteUpArr);
		$totalDownvote = count($voteDownArr);
		
		$totalVotes += $totalUpvote;
		
		if ($k >= 30)
		{
			continue;
		}
		
		//if (count($voteUpArr) - count($voteDownArr) > $highestVote)
		//{
			//$highestVote = count($voteUpArr) - count($voteDownArr);
		//}
		
		$querystring = "select * from dojos where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		$dojoInfo = array();
		while($row = mysqli_fetch_assoc($result)) {
			$dojoInfo[] = $row; 
		}
		
		$querystring = "select * from roster2 where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		$rosterInfo = array();
		while($row = mysqli_fetch_assoc($result)) {
			$rosterInfo[] = $row; 
		}
		
		$specifichash = $username . $posthash;
		
		$querystring = "select * from voted where (hash like '%$specifichash%');";
		$result = mysqli_query($link, $querystring);
		$userVote = array();
		while($row = mysqli_fetch_assoc($result)) {
			$userVote[] = $row; 
		}
		
		$querystring = "select * from commentboard where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$commentboard = array();
		while($row = mysqli_fetch_assoc($result)) {
			$commentboard[] = $row; 
		}
		
		$querystring = "select * from reposts where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$repostInfo = array();
		while($row = mysqli_fetch_assoc($result)) 
		{
			$repostInfo[] = $row;
		}
		
		array_push($intermediate,$personPosts[$k]);
		array_push($intermediate,$totalUpvote);
		array_push($intermediate,$totalDownvote);
		array_push($intermediate,$dojoInfo[0]);
		array_push($intermediate,$userVote);
		array_push($intermediate,$dojoInfo);
		array_push($intermediate,count($rosterInfo));
		array_push($intermediate,(count($commentboard) - count($repostInfo) - 1));
		array_push($intermediate,count($repostInfo));
		array_push($legitbased,$intermediate);
	}
	
	array_push($otherPersonInfo, $totalVotes);
	
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
		
		$userPosts = array();
		$querystring = "select * from posts where (username='$followeremail');";
		$result = mysqli_query($link, $querystring);
		while($row = mysqli_fetch_assoc($result)) 
		{
			$userPosts[] = $row; 
		}
		
		$birds = array();
		$upvoteTotal = 0;
		$downvoteTotal = 0;
		$totalPoints = 0;
		
		for ($i=0;$i<count($userPosts);$i++)
		{
			$userposthash = $userPosts[$i]['posthash'];
			
			if (in_array($userposthash, $birds))
			{
				continue;
			}
			else
			{
				$birds[] = $userposthash;
			}
			
			$querystring = "select * from voted where (hash like '%$userposthash%' and vote=1);";
			$result = mysqli_query($link, $querystring);
			$userUpArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$userUpArr[] = $row;
			}
		
			$querystring = "select * from voted where (hash like '%$userposthash%' and vote=0);";
			$result = mysqli_query($link, $querystring);
			$userDownArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$userDownArr[] = $row; 
			}
			
			$upvoteTotal += count($userUpArr);
			$downvoteTotal += count($userDownArr);
		}
		
		$totalPoints = $upvoteTotal - $downvoteTotal;
		
		array_push($intermediate,$personInfo2);
		array_push($intermediate,$totalPoints);
		array_push($followerArr,$intermediate);
	}
	
	array_push($totalPayload,$legitbased);
	array_push($totalPayload,$followerArr);
	array_push($totalPayload,$otherPersonInfo); // 0 is points, 1 is follower count
	array_push($totalPayload,$personInfo);
	
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>
