<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$description = $json_obj['description']; 
	$description = mysqli_real_escape_string($link,$description);
	
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
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$posterInfo = array();
	while($row = mysqli_fetch_assoc($result)) {
		$posterInfo[] = $row; 
	}
	$posterName = $posterInfo[0]['fullname'];

	$currdate = date('Y-m-d H:i:s');
	$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$querystring = "insert into commentest (username, posthash, seen, changed) values ('$username', '$posthash', 'yes', '$currdate');";
	$result = mysqli_query($link, $querystring);
	
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
			
		}
		else
		{
			$querystring = "select from posts where (dojohash='$dojohash' and posthash='$posthash');";
			$result = mysqli_query($link, $querystring);
			$testArr[] = array();
			while($row = mysqli_fetch_assoc($result)) {
				$testArr[] = $row; 
			}
			if (!empty($testArr[0]))
			{
				continue;
			}
			
			$querystring = "update dojos set made='$currdate' where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "select * from dojos where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($row = mysqli_fetch_assoc($result)) {
				$dojoInfo[] = $row; 
			}
			$dojoName = $dojoInfo[0]['dojo'];
			
			$querystring = "insert into posts (dojohash, username, description, likes, posttime, posthash, uploaded, hates, loves) values ('$dojohash', '$username', '$description', 0, '$currdate', '$posthash', 'yes',0,0);";
			$result = mysqli_query($link, $querystring);
			$resultArr[] = $result;
			echo(json_encode($result));
			
			//add post to freshset so they can see how many they havent seen
			$querystring = "select username from roster2 where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$users = array();
			while($row = mysqli_fetch_array($result)) {
				$users[] = $row; 
			}
			
			$querystring = "select follower from sheep where (leader='$username');";
			$result = mysqli_query($link, $querystring);
			$followers = array();
			while($row = mysqli_fetch_array($result)) {
				$followers[] = $row; 
			}
			
			for ($k = 0; $k<count($users);$k++)
			{
				$eachemail = $users[$k][0];
				
				$foundThat = false;
				$p = 0;
				while ($p<count($followers))
				{
					if ($foundThat)
					{
					
					}
					else
					{
						$followerusername = $followers[$p][0];
						if ($followerusername === $eachemail)
						{
							unset($followers[$p]);
							$followers = array_values($followers);
							$foundThat = true;
						}
					}
					$p++;
				}
				
				$eachemail = mysqli_real_escape_string($link,$eachemail);
				if ($eachemail === $username)
				{
					// do fuck all
					$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'post', ' posted to $dojoName', 'no', '$dojohash', '$posthash');";
					$result = mysqli_query($link, $querystring);
				}
				else
				{
					$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'post', ' posted to $dojoName', 'no', '$dojohash', '$posthash');";
					$result = mysqli_query($link, $querystring);
					//end that notification mf
				}
			}
			
			for ($k = 0; $k<count($followers);$k++)
			{
				$eachemail = $followers[$k][0];
				$eachemail = mysqli_real_escape_string($link,$eachemail);
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'post', ' posted to $dojoName', 'no', '$dojohash', '$posthash');";
				$result = mysqli_query($link, $querystring);
				//end that notification mf
			}
			
			$length = 20;
			$messagehash = '';
			$count = strlen($charset);
			while ($length--) {
				$messagehash .= $charset[mt_rand(0, $count-1)];
			}
			
			$querystring = "insert into commentboard (username, posthash, message, messagehash, made) values ('$username', '$posthash', ' posted to $dojoName', '$messagehash', '$currdate');";
			$result = mysqli_query($link, $querystring);
		}
	}
	echo(json_encode($resultArr));
	mysqli_close($link);	
?>