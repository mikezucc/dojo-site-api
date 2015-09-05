<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	$message = $json_obj['message']; 
	$message = mysqli_real_escape_string($link,$message);
	$messagehash = $json_obj['messagehash']; 
	$messagehash = mysqli_real_escape_string($link,$messagehash);
	
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
	
	$querystring = "insert into commentboard (username, posthash, message, messagehash, made) values ('$username', '$posthash', '$message', '$messagehash', '$currdate');";
	$result = mysqli_query($link, $querystring);
	if ($result)
	{
		echo(json_encode('posted\n'));
	}
	else
	{
		echo(json_encode('couldnt post\n'));
	}
	
	$findme   = '!flag';
	$pos = strpos($message, $findme);

	// The !== operator can also be used.  Using != would not work as expected
	// because the position of 'a' is 0. The statement (0 != false) evaluates 
	// to false.
	if ($pos !== false) 
	{
		$querystring = "select * from posts where (posthash='$posthash') limit 1;";
		$result = mysqli_query($link, $querystring);	
		$poBoy = array();
		while($row = mysqli_fetch_assoc($result)) {
			$poBoy[] = $row; 
		}
		$dojohash = $poBoy[0]['dojohash'];
	
		$querystring = "select * from ones where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);	
		$creatorBoy = array();
		while($row = mysqli_fetch_assoc($result)) {
			$creatorBoy[] = $row; 
		}
		$creatorEmail = $creatorBoy[0]['username'];
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$creatorEmail', '$username', 'comment', ' flagged a post in your dojo', 'no', '$posthash', '$messagehash');";
		$result = mysqli_query($link, $querystring);
		
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', 'VH03yD97um98fQ43KB54EF20', '$username', 'comment', ' flagged a post!!!!', 'no', '$posthash', '$messagehash');";
		$result = mysqli_query($link, $querystring);
		
		// automatically authenticate user
		$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$length = 30;
		$stoninOBrian = '';
		$count = strlen($charset);
		while ($length--) 
		{
			$stoninOBrian .= $charset[mt_rand(0, $count-1)];
		}
		
		$querystring = "insert into commentboard (username, posthash, message, messagehash, made) values ('VH03yD97um98fQ43KB54EF20', '$posthash', 'The Dojo moderator and Dojo Administrator has been notified! Thank you for your vigilance!', '$stoninOBrian', '$currdate');";
		$result = mysqli_query($link, $querystring);
		
		$querystring = "insert into reported (perp, filer, complaintid, complaint, made) values ('', '$posthash', 'The Dojo moderator and Dojo Administrator has been notified! Thank you for your vigilance!', '$stoninOBrian', '$currdate');";
		$result = mysqli_query($link, $querystring);
	}
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);	
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row; 
	}
	$notiName = $notiBOY[0]['fullname'];

	//add post to freshset so they can see how many they havent seen
	$querystring = "select * from commentest where (posthash='$posthash');";
	$result = mysqli_query($link, $querystring);	
	$commentators = array();
	while($row = mysqli_fetch_assoc($result)) {
		$commentators[] = $row; 
	}
	
	for ($k = 0; $k<count($commentators);$k++)
	{
		$specificEmail = $commentators[$k]['username'];
		if ($specificEmail === $username)
		{
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
				$querystring = "insert into commentest (username, posthash, seen, changed) values ('$username', '$posthash', 'no', '$currdate');";
				$result = mysqli_query($link, $querystring);
			}
		}
		else
		{
			//do some notification swag insert here rull qwuk
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$specificEmail', '$username', 'comment', ' also commented on a post', 'no', '$posthash', '$messagehash');";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
		}
		
		// user is there
		$querystring = "update commentest set seen='no' where (username='$specificEmail' and posthash='$posthash');";
		$result = mysqli_query($link, $querystring);
		if ($result)
		{
			echo(json_encode("updated for '$specificEmail'"));
		}
		else
		{
			echo(json_encode("failed update for '$specificEmail'"));
		}
	}
	mysqli_close($link);
?>
