<?php

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
	
	$querystring = "select * from noti2 where (forwhom='$username' and seen='no');";
	$result = mysqli_query($link, $querystring);
	$notificationResults = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notificationResults[] = $row; 
		$instigator = $notificationResults[count($notificationResults)-1]['instigator'];
		$querystringMini = "select * from users where (username='$instigator');";
		$resultMini = mysqli_query($link, $querystringMini);
		$instigatinfo = array();
		while($queryresponse = mysqli_fetch_assoc($resultMini))
		{
			$instigatinfo[] = $queryresponse;
		}
		if (empty($instigatinfo))
		{
		 continue;
		}
		
		$instaName = $instigatinfo[0]['fullname'];
		
		$notificationResults[count($notificationResults)-1]['payload'] = $instaName . $notificationResults[count($notificationResults)-1]['payload'];
		
	}
	
	//$querystring = "update noti2 set seen='yes' where (email='$email');";
	//$result = mysqli_query($link, $querystring);

	echo(json_encode($notificationResults));	
	mysqli_close($link);
?>