<?php 
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username'];
	$username = mysqli_real_escape_string($link,$username);
	
	if (empty($email))
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
	
	$querystring = "delete from messageboard where username='$username';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from messagest where username='$username';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from commentboard where username='$username';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from commentest where username='$username';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from noti2 where (instigator='$username' or forwhom='$username');";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from roster2 where username='$username';";
	$result = mysqli_query($link, $querystring);
	$querystring = "delete from users where email='$username';";
	$result = mysqli_query($link, $querystring);
	
	echo(json_encode($result));
			mysqli_close($link);
?>
