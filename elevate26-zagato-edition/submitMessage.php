<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
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
	
	$querystring = "insert into messageboard (username, dojohash, message, messagehash, made) values ('$username', '$dojohash', '$message', '$messagehash', '$currdate');";
	$result = mysqli_query($link, $querystring);
	if ($result)
	{
		echo(json_encode('posted\n'));
	}
	else
	{
		echo(json_encode('couldnt post\n'));
	}
	
	$querystring = "select * from dojos where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$dojoInfo = array();
	while($row = mysqli_fetch_assoc($result)) {
		$dojoInfo[] = $row; 
	}
	$dojoName = $dojoInfo[0]['dojo'];
	
	$findme   = '!flag';
	$pos = strpos($message, $findme);

	// The !== operator can also be used.  Using != would not work as expected
	// because the position of 'a' is 0. The statement (0 != false) evaluates 
	// to false.
	if ($pos !== false) 
	{
	
		$querystring = "select * from ones where (dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);	
		$creatorBoy = array();
		while($row = mysqli_fetch_assoc($result)) {
			$creatorBoy[] = $row; 
		}
		$creatorEmail = $creatorBoy[0]['username'];
		//do some notification swag insert here rull qwuk
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$creatorEmail', '$username', 'message', ' flagged something in $dojoName', 'no', '$dojohash', '$messagehash');";
		$result = mysqli_query($link, $querystring);
		//end that notification mf
		
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', 'VH03yD97um98fQ43KB54EF20', '$username', 'message', ' flagged something in $dojoName!!!!', 'no', '$dojohash', '$messagehash');";
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
		
		$querystring = "insert into messageboard (username, dojohash, message, messagehash, made) values ('VH03yD97um98fQ43KB54EF20', '$dojohash', 'The Dojo moderator and Dojo Administrator has been notified! Thank you for your vigilance!', '$stoninOBrian', '$currdate');";
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
	$querystring = "select * from roster2 where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$users = array();
	while($row = mysqli_fetch_assoc($result)) {
		$users[] = $row; 
	}
	
	for ($k = 0; $k<count($users);$k++)
	{
		$specificEmail = $users[$k]['username'];
		if ($specificEmail === $username)
		{
			// do fuck all
		}
		else
		{
			//do some notification swag insert here rull qwuk
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$specificEmail', '$username', 'message', ' messaged $dojoName', 'no', '$dojohash', '$messagehash');";
			$result = mysqli_query($link, $querystring);
			//end that notification mf
		}
		
		$querystring = "select * from messagest where (dojohash='$dojohash' and username='$specificEmail');";
		$result = mysqli_query($link, $querystring);	
		$arr = array();
		while($row = mysqli_fetch_assoc($result)) {
			$arr[] = $row; 
		}
		if (empty($arr[0]))
		{
			// user not have entry in messagest
			$querystring = "insert into messagest (username, dojohash, seen, changed) values ('$specificEmail', '$dojohash', 'no', '$currdate');";
			$result = mysqli_query($link, $querystring);
			if ($result)
			{
				echo(json_encode("inserted for '$specificEmail'"));
			}
			else
			{
				echo(json_encode("failed insert for '$specificEmail'"));
			}
		}
		else
		{
			// user is there
			$querystring = "update messagest set seen='no' where (username='$specificEmail' and dojohash='$dojohash');";
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
	}
	mysqli_close($link);
?>
