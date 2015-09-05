<?php 

	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$description = $json_obj['description']; 
	$description = mysqli_real_escape_string($link,$description);
	
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
	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);	
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row; 
	}
	$notiName = $notiBOY[0]['fullname'];
	
	$querystring = "select * from posts where (posthash='$posthash') limit 1;";
	$result = mysqli_query($link, $querystring);
	$originalPost = array();
	while($row = mysqli_fetch_assoc($result)) {
		$originalPost[] = $row; 
	}
	$originalPoster = $originalPost[0]['username'];
	if ($eachemail === $originalPoster)
	{
		// do fuck all
	}
	else
	{
		//do some notification swag insert here rull qwuk
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$originalPoster', '$username', 'repost', ' spread your post!', 'no', '$dojohash', '$posthash');";
		$result = mysqli_query($link, $querystring);
		//end that notification mf
	}
	
	$resultArr = array();
	
	$dojounlim = $json_obj['dojos'];
	$dojos = explode(",",$dojounlim);
	$numOfDojos = count($dojos);
	for ($i=0;$i<$numOfDojos;$i++)
	{
		$dojohash = $dojos[$i];
		$dojohash = mysqli_real_escape_string($link,$dojohash);
		if (empty($dojohash))
		{
			continue;
		}
		else
		{
			$querystring = "select * from dojos where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($row = mysqli_fetch_assoc($result)) {
				$dojoInfo[] = $row; 
			}
			$dojoName = $dojoInfo[0]['dojo'];
		
			$querystring = "select * from posts where (dojohash='$dojohash' and posthash='$posthash');";
			$result = mysqli_query($link, $querystring);
			$testArr = array();
			while($row = mysqli_fetch_assoc($result)) {
				$testArr[] = $row; 
			}
			if (!empty($testArr[0]))
			{
				continue;
			}
			
			$querystring = "insert into reposts  (posthash, entropy, dojohash, reposted, original) values ('$posthash', '$username','$dojohash', '$currdate', '$originalPoster');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "insert into posts (dojohash, username, description, likes, posttime, posthash, uploaded, hates, loves) values ('$dojohash', '$originalPoster', '$description', 0, '$currdate', '$posthash', 'yes',0,0);";
			$result = mysqli_query($link, $querystring);
			$resultArr[] = $result;
			echo(json_encode($result));
			
			//add post to freshset so they can see how many they havent seen
			$querystring = "select * from roster2 where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$users = array();
			while($row = mysqli_fetch_assoc($result)) {
				$users[] = $row; 
			}
			
			for ($k = 0; $k<count($users);$k++)
			{
				$eachemail = $users[$k]['username'];
				if (($eachemail === $originalPoster) || ($eachemail === $username))
				{
					continue;
				}
				//do some notification swag insert here rull qwuk
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'repost', ' reposted to $dojoName', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
			
			$length = 20;
			$messagehash = '';
			$count = strlen($charset);
			while ($length--) {
				$messagehash .= $charset[mt_rand(0, $count-1)];
			}
		
			$querystring = "insert into commentboard (username, posthash, message, messagehash, made) values ('$username', '$posthash', ' reposted to $dojoName', '$messagehash', '$currdate');";
			$result = mysqli_query($link, $querystring);
			
			// do fuck all
			$querystring = "select * from commentest where (username='$username');";
			$result = mysqli_query($link, $querystring);	
			$testCommenter = array();
			while($row = mysqli_fetch_assoc($result)) {
				$testCommenter[] = $row; 
			}
			
			if(!empty($testCommenter))
			{
				// already in commentest, no need to add
			}
			else
			{
				// need to add
				$querystring = "insert into commentest  (username, posthash, seen, changed) values ('$username', '$posthash', 'no', '$currdate');";
				$result = mysqli_query($link, $querystring);
			}
			
		}
	}
	echo(json_encode($resultArr));
	mysqli_close($link);	
?>