<?php
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$nodehash = $json_obj['nodehash']; 
	$nodehash = mysqli_real_escape_string($link,$nodehash);
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
	
	$querystring = "insert into seshboard (username, nodehash, message, messagehash, made, glory) values ('$username', '$nodehash', '$message', '$messagehash', '$currdate', 0);";
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
		$querystring = "select * from nodes where (nodehash='$nodehash') limit 1;";
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
		
		$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', 'creatorEmail', '$username', 'comment', ' flagged a post in your dojo', 'no', '$nodehash', '$messagehash');";
		$result = mysqli_query($link, $querystring);
		
		$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', 'VH03yD97um98fQ43KB54EF20', '$username', 'comment', ' flagged a post!!!!', 'no', '$nodehash', '$messagehash');";
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
		
		$querystring = "insert into seshboard (username, nodehash, message, messagehash, made) values ('VH03yD97um98fQ43KB54EF20', '$nodehash', 'The Dojo moderator and Dojo Administrator has been notified! Thank you for your vigilance!', '$stoninOBrian', '$currdate');";
		$result = mysqli_query($link, $querystring);
		
		$querystring = "insert into reported (perp, filer, complaintid, complaint, made) values ('', '$nodehash', 'The Dojo moderator and Dojo Administrator has been notified! Thank you for your vigilance!', '$stoninOBrian', '$currdate');";
		$result = mysqli_query($link, $querystring);
	}
	
	mysqli_close($link);
?>
