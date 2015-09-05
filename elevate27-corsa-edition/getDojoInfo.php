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
	
	$currdate = date('Y-m-d H:i:s');
	$currdateFormat = new DateTime($currdate);
	
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

	$dojohash = $json_obj['dojohash'];
	$dojohash = mysqli_real_escape_string($link,$dojohash);

	//changed the "dojos" table eventually
	$querystring = "select * from dojos where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$dojoInfo = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$dojoInfo[] = $row;
	}

	$querystring = "select * from posts where (dojohash='$dojohash') order by posttime desc limit 40;";
	$result = mysqli_query($link, $querystring);
	$postList = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$postList[] = $row;
	}
	
	$legitbased = array();
	$voteArray = array();
	$posterArray = array();
	$postListUnsorted = array();
	$hashArray = array();
	$highestVote = 0;

	for ($k=0;$k<count($postList);$k++)
	{
		$intermediate = array();
		$totalUpvote = 0;
		$totalDownvote = 0;
		$useremail = $postList[$k]['username'];
		$posthash = $postList[$k]['posthash'];
		$querystring = "select * from users where (username='$useremail');";
		$result = mysqli_query($link, $querystring);
		$userblock = array();
		while($row = mysqli_fetch_assoc($result)) {
				$userblock[] = $row;
		}
		
		array_push($hashArray, $posthash);
	
		unset($posterArray["$useremail"]);
		$posterArray["$useremail"] = $userblock;
		
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
		
		$specifichash = $username . $posthash;
		
		$querystring = "select * from voted where (hash like '%$specifichash%');";
		$result = mysqli_query($link, $querystring);
		$userVote = array();
		while($row = mysqli_fetch_assoc($result)) {
			$userVote[] = $row; 
		}
		$highestVote = $voteUpArr - $voteDownArr;
		
		$querystring = "select * from commentest where (posthash='$posthash' and username='$username');";
		$result = mysqli_query($link, $querystring);
		$commentest = array();
		while($row = mysqli_fetch_assoc($result)) {
			$commentest[] = $row; 
		}
		
		$querystring = "select * from commentboard where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$commentboard = 0;
		while($row = mysqli_fetch_assoc($result)) {
			$commentboard++; 
		}
		
		$madeTime = $postList[$k]['posttime'];
		$madeTimeFormat = new DateTime($madeTime);
		

		  $month1 = $madeTimeFormat->format('m'); 
		  $month2 = $currdateFormat->format('m'); 
		  $month = abs($month2 - $month1);

		  $strang = "never ago";
		  if($month > 0) 
		  { 
				if ($month == 1)
				{
					$strang = "a month ago";
				}
				else
				{
					$strang = $month . " months ago";
				}
		  } 
		  else
		  {
			  $day1 = $madeTimeFormat->format('d'); 
			  $day2 = $currdateFormat->format('d'); 
			  $day = abs($day2 - $day1);
		  
				if($day > 0) 
				{ 
					if ($day == 1)
					{
						$strang = "a day ago";
					}
					else
					{
						$strang = $day . " days ago"; 
					}
					if ($day > 5)
					{
						$strang = "ABOUT A WEEK AGO";
					}
				}
				else
				{
					$h1 = $madeTimeFormat->format('h'); 
				  $h2 = $currdateFormat->format('h'); 
				  $H = abs($h2 - $h1);
			  
					if($H > 0)
					{
						if ($H == 1)
						{
							$strang = "an hour ago";
						}
						else
						{
							$strang = $H . " hours ago";
						}
					}
					else
					{
				
					  $i1 = $madeTimeFormat->format('i'); 
					  $i2 = $currdateFormat->format('i'); 
					  $mins = abs($i2 - $i1);

						if($mins > 0)
						{ 
							if ($mins == 1)
							{
								$strang = "been a minute";
							}
							else
							{
								$strang = $mins . " mins ago";
							}
						}
						else
						{
							$strang = "just now"; 
						}
					}
				}
		  }
		  
		$querystring = "select * from reposts where (posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		$repostInfo = 0;
		while($row = mysqli_fetch_assoc($result)) 
		{
			$repostInfo++;
		}
		
		$fuck = 0;
		if (($commentboard - $repostInfo - 1) > 0)
		{
			$fuck = ($commentboard - $repostInfo - 1);
		}
		else
		{
			$fuck = 0;
		}
		
		array_push($intermediate,$postList[$k]);
		array_push($intermediate,$userblock);
		array_push($intermediate,$totalUpvote);
		array_push($intermediate,$totalDownvote);
		array_push($intermediate,$userVote);
		array_push($intermediate,$commentest);
		array_push($intermediate, $fuck);
		array_push($intermediate, $strang);
		array_push($intermediate, count($repostInfo));
		array_push($legitbased,$intermediate);
		
		$foundIt = false;
		for ($j=0;$j<count($postListUnsorted);$j++)
		{
			$pointValue = $postListUnsorted[$j];
			if ($highestVote == $pointValue)
			{
				$foundIt = true;
			}
		}
		if ($foundIt)
		{
			continue;
		}
		else
		{
			$voteArray["$highestVote"] = $intermediate;
			$postListUnsorted[] = $highestVote;
		}
		/*
		if (in_array($highestVote, $voteArray))
		{
			//$voteArray["$highestVote"] = $intermediate;
		}
		else
		{
		//unset($voteArray["$highestVote"]);
			$voteArray["$highestVote"] = $intermediate;
		}
		*/
	}
	
	krsort($voteArray);
	$topbased = array_values($voteArray);
	
	//echo(json_encode($topbased));
	//return;
	
	$creator = array();
	$querystring = "select * from ones where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	while($row = mysqli_fetch_assoc($result)) {
		$creator[] = $row;
	}
	
	if (empty($creator))
	{
		$temp = array();
		$temp['username'] = 'doji@getdojo.co';
		$temp['dojohash'] = $dojohash;
		array_push($creator,$temp);
	}
	
	$posterValues = array_values($posterArray);
	$roster = array();
	$listOfSayers = array();
	for ($k=0;$k<count($posterValues);$k++)
	{
		$intermediate = array();
		$user = $posterValues[$k][0];
		$useremail = $posterValues[$k][0]['username'];
		$listOfSayers[] = $useremail;
		$userGrade = 0;
		$downvoteTotal = 0;
		$upvoteTotal = 0;
		
		$userPosts = array();
		$querystring = "select * from posts where (username='$useremail');";
		$result = mysqli_query($link, $querystring);
		while($row = mysqli_fetch_assoc($result)) {
			$userPosts[] = $row; 
		}
		
		$upvoteTotal = 0;
		$downvoteTotal = 0;
		$birds = array();
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
		
		$userGrade = abs($upvoteTotal - $downvoteTotal);
		
		$isCreator = 'no';
		if ($useremail === $creator[0]['username'])
		{
			$isCreator = 'yes';
		}
		
		array_push($intermediate,$user);
		array_push($intermediate,$userGrade);
		array_push($intermediate,$isCreator);
		array_push($roster,$intermediate);
	}
	
	$rosterTest = array();
	$querystring = "select * from roster2 where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	while($row = mysqli_fetch_assoc($result)) {
		$rosterTest[] = $row;
	}
	
	for ($p=0;$p<count($rosterTest);$p++)
	{
		$intermediate = array();
		$pemail = $rosterTest[$p]['username'];
		$foundIt = false;
		for ($j=0;$j<count($listOfSayers);$j++)
		{
			$minimail = $listOfSayers[$j];
			if ($minimail === $pemail)
			{
				$foundIt = true;
			}
		}
		if ($foundIt)
		{
			continue;
		}
		else
		{
			$querystring = "select * from users where (username='$pemail');";
			$result = mysqli_query($link, $querystring);
			$userblock = array();
			while($row = mysqli_fetch_assoc($result)) {
				$userblock[] = $row;
		}
		
		$user = $userblock[0];
		$userPosts = array();
		$querystring = "select * from posts where (username='$pemail');";
		$result = mysqli_query($link, $querystring);
		while($row = mysqli_fetch_assoc($result)) {
			$userPosts[] = $row; 
		}
		
		$upvoteTotal = 0;
		$downvoteTotal = 0;
		$birds = array();
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
			$userUpArr = 0;
			while($row = mysqli_fetch_assoc($result)) {
				$userUpArr++;
			}
		
			$querystring = "select * from voted where (hash like '%$userposthash%' and vote=0);";
			$result = mysqli_query($link, $querystring);
			$userDownArr = 0;
			while($row = mysqli_fetch_assoc($result)) {
				$userDownArr++; 
			}
			
			$upvoteTotal += $userUpArr;
			$downvoteTotal += $userDownArr;
		}
		
		$userGrade = abs($upvoteTotal - $downvoteTotal);
		
		$isCreator = 'no';
		if ($pemail === $creator[0]['username'])
		{
			$isCreator = 'yes';
		}
		
		array_push($intermediate,$user);
		array_push($intermediate,$userGrade);
		array_push($intermediate,$isCreator);
		array_push($roster,$intermediate);
		}
	}
	
	
	// set all these messages to seen
	$querystring = "select * from messagest where (dojohash='$dojohash' and username='$username');";
	$result = mysqli_query($link, $querystring);
	$messageSWAGNIGGA = array();
	while($row = mysqli_fetch_assoc($result)) {
		$messageSWAGNIGGA[] = $row; 
	}
	
	$querystring = "select status from roster2 where (username='$username' and  dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$followSwag = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$followSwag[] = $row; 
	}
	$followingSwegSweg = "not";
	if (!empty($followSwag))
	{
		$followingSwegSweg = $followSwag[0]['status'];
	}
	
	$totalPayload = array();
	
	if (empty($followSwag))
	{
		array_push($totalPayload,"not");
	}
	else
	{
		array_push($totalPayload,"following");
	}
	
	$totalPayload = array();
	
	array_push($totalPayload, $dojoInfo);
	array_push($totalPayload, $legitbased);
	array_push($totalPayload, $topbased);
	array_push($totalPayload, $roster);
	array_push($totalPayload, $creator[0]);
	array_push($totalPayload, $messageSWAGNIGGA);
	array_push($totalPayload, $followingSwegSweg);
	array_push($totalPayload, $hashArray);
	
	echo(json_encode($totalPayload));
	
	mysqli_close($link);
?>
