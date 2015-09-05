<?php 
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$description = $json_obj['bio']; 
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
	
	$querystring = "update users set bio='$description' where (username='$username');";
	$result = mysqli_query($link, $querystring);
	
	echo($result);
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row;
	}
	$notiName = $notiBOY[0]['fullname'];
	
	$querystring = "select * from sheep where (leader='$username');";
	$result = mysqli_query($link, $querystring);
	$followers = array();
	while($row = mysqli_fetch_assoc($result)) {
		$followers[] = $row; 
	}
	
	$currdate = date('Y-m-d H:i:s');
	
	for ($k = 0; $k<count($followers);$k++)
	{
		$specificEmail = $followers[$k]['follower'];
		//do some notification swag insert here rull qwuk
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$specificEmail', '$username', 'bio', ' updated their bio', 'no', '', '');";
		$result = mysqli_query($link, $querystring);
		//end that notification mf
	}
	
	
	mysqli_close($link);
	
?>
