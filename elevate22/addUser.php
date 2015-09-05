<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
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
	
	$fullname = $json_obj['fullname'];
	$fullname = mysqli_real_escape_string($link,$fullname);
	$salt1 = $json_obj['salt1'];
	$salt1 = mysqli_real_escape_string($link,$salt1);
	

	//changed the "dojos" table eventually
	$querystring = "select * from users where (fullname='$fullname');";
	$result = mysqli_query($link, $querystring);
	$user = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$user[] = $queryresponse;	
	}
	
	$currdate = date('Y-m-d H:i:s');
	
	$totalPayload = array();

	if (empty($user[0]))
	{
		//dojos must be CHANGED
		$querystring = "insert into users (fullname, made, username, profilehash, bio) values ('$fullname', '$currdate', '$username','','');";
		$result = mysqli_query($link,$querystring);
		//echo("made");
		
		// add to salt table
		$querystring = "insert into salts (salt1, username, salt2, made) values ('$salt1', '$username', '', '$currdate');";
		$result = mysqli_query($link, $querystring);
		
		// automatically authenticate user
		$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$length = 30;
		$token = '';
		$count = strlen($charset);
		while ($length--) 
		{
			$token .= $charset[mt_rand(0, $count-1)];
		}
		$querystring = "insert into token (token, username, made) values ('$token', '$username', '$currdate');";
		$result = mysqli_query($link, $querystring);
		
		$querystring = "select * from token where (username='$username');";
		$result = mysqli_query($link, $querystring);
		$tokenArr = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$tokenArr[] = $queryresponse;	
		}
		
		array_push($totalPayload,"made");
		array_push($totalPayload,$tokenArr);
		
		$querystring = "insert into sheep (leader, follower, status, made) values ('$username','VH03yD97um98fQ43KB54EF20','following','$currdate');"; 
		$result = mysqli_query($link, $querystring);
		if($result == true)
		{
			$querystring = "select * from users where (username='VH03yD97um98fQ43KB54EF20');";
			$result = mysqli_query($link, $querystring);	
			$notiBOY = array();
			while($row = mysqli_fetch_assoc($result)) {
				$notiBOY[] = $row;
			}
			$notiName = $notiBOY[0]['fullname'];
		
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$username', 'VH03yD97um98fQ43KB54EF20', 'followyou', ' is following you!', 'no', '', '');";
			$result = mysqli_query($link, $querystring);
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$username', 'VH03yD97um98fQ43KB54EF20', 'message', ' to report content or a user, type !flag into a comment or message', 'no', 'TA76dY31Sc96Mu38', 'lkjfdshg886sdfgsd8f6gsdfg');";
			$result = mysqli_query($link, $querystring);
		}
		
		$querystring = "select * from roster2 where (dojohash='TA76dY31Sc96Mu38');";
		$result = mysqli_query($link, $querystring);	
		$dojoPeeps = array();
		while($row = mysqli_fetch_assoc($result)) {
			$dojoPeeps[] = $row; 
		}
	
		for ($j = 0;$j<count($dojoPeeps);$j++)
		{
			$dojoUN = $dojoPeeps[$j]['username'];
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$dojoUN', '$username', 'followdojo', ' followed The Breakfast Club', 'no', '', 'TA76dY31Sc96Mu38');";
			$result = mysqli_query($link, $querystring);
		}
		
		$querystring = "insert into roster2 (dojohash, username, status, made) values ('TA76dY31Sc96Mu38','$username','following','$currdate');"; 
		$result = mysqli_query($link, $querystring);
		
		//$querystring = "insert into notifi2 (email, seen, changed) values ('$email', 'yes', '$currdate');";
		//$result = mysqli_query($link, $querystring);
	}
	else
	{
		//user does exist, do nothing
		array_push($totalPayload,"exists!");

	}
	echo(json_encode($totalPayload));
	mysqli_close($link);
?>
