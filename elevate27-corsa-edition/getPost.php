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
	$posthash = $json_obj['posthash'];
	$posthash = mysqli_real_escape_string($link,$posthash);
	//echo(json_encode($posthash));
	//echo(json_encode($dojohash));
	$querystring = "select * from posts where (dojohash='$dojohash' and posthash='$posthash');";
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

	$intermediate = array();
	$totalUpvote = 0;
	$totalDownvote = 0;
	//echo(json_encode($postList));
	$useremail = $postList[0]['username'];
	$querystring = "select * from users where (username='$useremail');";
	$result = mysqli_query($link, $querystring);
	$userblock = array();
	while($row = mysqli_fetch_assoc($result)) {
			$userblock[] = $row;
	}
	
	$querystring = "select * from dojos where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$dojoblock = array();
	while($row = mysqli_fetch_assoc($result)) {
			$dojoblock[] = $row;
	}
	
	array_push($hashArray, $posthash);
	
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
	
	$madeTime = $postList[0]['posttime'];
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
	
	array_push($intermediate,$postList[0]);
	array_push($intermediate,$userblock);
	array_push($intermediate,$totalUpvote);
	array_push($intermediate,$totalDownvote);
	array_push($intermediate,$userVote);
	array_push($intermediate,$commentest);
	array_push($intermediate, $fuck);
	array_push($intermediate, $strang);
	array_push($intermediate, count($repostInfo));
	array_push($intermediate, $dojoblock);
	array_push($legitbased,$intermediate);
	
	echo(json_encode($legitbased));
	
	mysqli_close($link);
?>
