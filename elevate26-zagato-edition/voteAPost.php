<?php 
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$nodehash = $json_obj['nodehash']; 
	$nodehash = mysqli_real_escape_string($link,$nodehash);
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
	
	$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
	$result = mysqli_query($link, $querystring);	
	$opposts = array();
	while($row = mysqli_fetch_assoc($result)) {
		$opposts[] = $row; 
	}
	$op = $opposts[0]['username'];
	
	$hash = $username . $nodehash;
	
	$querystring = "select * from voted420 where (hash='$hash');";
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
			$querystring = "delete from voted420 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory--;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat--;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			//end that notification mf
		}
		if (($vote == 0) && ($voteSubmitted == 'down'))
		{
			// do nothing already voted
			$querystring = "delete from voted420 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory++;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat++;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			//end that notification mf
		}
		if (($vote == 0) && ($voteSubmitted == 'up'))
		{
			$querystring = "update voted420 set vote=1 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory++;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat++;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' liked your post!', 'no', '$dojohash', '$nodehash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
		if (($vote == 1) && ($voteSubmitted == 'down'))
		{
			$querystring = "update voted420 set vote=0 where (hash='$hash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "delete from noti420 where payload like '%like%' and instigator='$username' and subject='$nodehash';";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory--;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat--;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
			
			}
			else
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' disliked your post! Rekt!', 'no', '$dojohash', '$nodehash');";
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
			$querystring = "insert into voted420 (hash, vote) values ('$hash',1);";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory++;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat++;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' liked your post!', 'no', '$dojohash', '$nodehash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
		if ($voteSubmitted == 'down')
		{
			// do nothing already voted
			$querystring = "insert into voted420 (hash, vote) values ('$hash',0);";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$nojoArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$nojoArr[] = $row; 
			}
			$nojo = $nojoArr[0];
			$nojoGlory = $nojo['honored'];
			$nojoGlory--;
			
			$querystring = "update nojo set honored=$nojoGlory where (nodehash='$nodehash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from users where (username='$op');";
			$result = mysqli_query($link, $querystring);
			$opGlory = array();
			while($row = mysqli_fetch_assoc($result)) {
				$opGlory[] = $row; 
			}
			$opObj = $opGlory[0];
			$opGloryDat = $opObj['glory'];
			$opGloryDat--;
			
			$querystring = "update users set glory=$opGloryDat where (username='$op');";
			$result = mysqli_query($link, $querystring);
			
			if ($username === $op)
			{
				
			}
			else
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$op', '$username', 'like', ' disliked your post! Rekt!', 'no', '$dojohash', '$nodehash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
		}
	}
	
?>
