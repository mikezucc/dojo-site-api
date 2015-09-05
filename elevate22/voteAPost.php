<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$voteSubmitted = $json_obj['vote']; 
	$voteSubmitted = mysqli_real_escape_string($link,$voteSubmitted);
	
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
	
	$currdate = date('Y-m-d H:i:s');
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);	
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row; 
	}
	$notiName = $notiBOY[0]['fullname'];
	
	$querystring = "select * from posts where (posthash='$posthash') limit 1;";
	$result = mysqli_query($link, $querystring);	
	$opposts = array();
	while($row = mysqli_fetch_assoc($result)) {
		$opposts[] = $row; 
	}
	$op = $opposts[0]['username'];
	
	
	$hash = $username . $posthash;
	
	$querystring = "select * from voted where (hash='$hash');";
	$result = mysqli_query($link, $querystring);
	$voteArr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$voteArr[] = $row; 
	}
	
	if (!empty($voteArr))
	{
		$vote = $voteArr[0]['vote'];
		if (($vote == 1) && ($voteSubmitted == 'up'))
		{
			// do nothing already voted
			$querystring = "delete from voted where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti2 where payload like '%like%' and instigator='$username' and subject='$posthash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
		}
		if (($vote == 0) && ($voteSubmitted == 'down'))
		{
			// do nothing already voted
			$querystring = "delete from voted where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti2 where payload like '%like%' and instigator='$username' and subject='$posthash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
		}
		if (($vote == 0) && ($voteSubmitted == 'up'))
		{
			$querystring = "update voted set vote=1 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti2 where payload like '%like%' and instigator='$username' and subject='$posthash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
			
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'repost', ' liked your post!', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
		if (($vote == 1) && ($voteSubmitted == 'down'))
		{
			$querystring = "update voted set vote=0 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti2 where payload like '%like%' and instigator='$username' and subject='$posthash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
			
			if ($username === $op)
			{
			
			}
			else
			{
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'repost', ' disliked your post! Rekt!', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
	}
	else
	{
		if ($voteSubmitted == 'up')
		{
			// do nothing already voted
			$querystring = "insert into voted (hash, vote) values ('$hash',1);";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'repost', ' liked your post!', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
		if ($voteSubmitted == 'down')
		{
			// do nothing already voted
			$querystring = "insert into voted (hash, vote) values ('$hash',0);";
			$result = mysqli_query($link, $querystring);
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'repost', ' disliked your post! Rekt!', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
	}
	
?>
