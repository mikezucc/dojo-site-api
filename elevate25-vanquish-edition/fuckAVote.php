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
	
	$nodehash = $json_obj['nodehash'];
	$nodehash = mysqli_real_escape_string($link,$nodehash);
	
	$voteSubmitted = $json_obj['vote'];
	$voteSubmitted = mysqli_real_escape_string($link,$voteSubmitted);
	
	$hash = $username . $nodehash;
	
	$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
	$result = mysqli_query($link, $querystring);	
	$opposts = array();
	while($row = mysqli_fetch_assoc($result)) {
		$opposts[] = $row; 
	}
	$op = $opposts[0]['username'];
	
	$querystring = "select * from nojovote where (hash='$hash');";
	$result = mysqli_query($link, $querystring);
	$voteArr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$voteArr[] = $row; 
	}
	
	if (!empty($voteArr))
	{
		$vote = $voteArr[0]['vote'];
		if (($vote === "y") && ($voteSubmitted === "up"))
		{
			// do nothing already voted
			$querystring = "delete from nojovote where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			//$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			//$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set honored=honored-1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory-1 where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			//end that notification mf
		}
		if (($vote == "n") && ($voteSubmitted === "down"))
		{
			// do nothing already voted
			$querystring = "delete from nojovote where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			//$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			//$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set dishonored=dishonored-1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory+1  where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			//end that notification mf
		}
		if (($vote == "n") && ($voteSubmitted === "up"))
		{
			$querystring = "update nojovote set vote='y', voted='$currdate' where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			//$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			//$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set honored=honored+1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set dishonored=dishonored-1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory+2 where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				//$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' liked your post!', 'no', '$dojohash', '$nodehash');";
				//$result = mysqli_query($link, $querystring);
			}
		}
		if (($vote == "y") && ($voteSubmitted == "down"))
		{
			$querystring = "update nojovote set vote='n', voted='$currdate' where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			//$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			//$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set honored=honored-1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set dishonored=dishonored+1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory-2 where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
			
			}
			else
			{
				//$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' disliked your post! Rekt!', 'no', '$dojohash', '$nodehash');";
				//$result = mysqli_query($link, $querystring);
			}
		}
	}
	else
	{
		if ($voteSubmitted === "up")
		{
			// do nothing already voted
			$querystring = "insert into nojovote (hash, vote, voted) values ('$hash', 'y', '$currdate');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set honored=honored+1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory+1 where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				//$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' liked your post!', 'no', '$dojohash', '$nodehash');";
				//$result = mysqli_query($link, $querystring);
			}
		}
		if ($voteSubmitted == 'down')
		{
			// do nothing already voted
			$querystring = "insert into nojovote (hash, vote, voted) values ('$hash', 'n', '$currdate');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update nojo set dishonored=dishonored+1 where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "update users set glory=glory-1 where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				//$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' disliked your post! Rekt!', 'no', '$dojohash', '$nodehash');";
				//$result = mysqli_query($link, $querystring);
			}
		}
	}
	
?>
