<?php 
	error_reporting(0);
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
	
	$totalPayload = array();
	
	$querystring = "select * from users where (username='$personemail');"; 
	$result = mysqli_query($link, $querystring);
	$personInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$personInfo[] = $queryresponse;
	}
	
	$person = $personInfo[0];
	
	$querystring = "select * from sheep where (leader='$username' and status='following');";
	$result = mysqli_query($link, $querystring);
	$followerRaw = array();
	while($row = mysqli_fetch_assoc($result)) {
		$followerRaw[] = $row; 
	}
	
	$followerArr = array();
	for ($k=0;$k<count($followerRaw);$k++)
	{
		$followeremail = $followerRaw[$k]['follower'];
		$querystring = "select * from users where (username='$followeremail');"; 
		$result = mysqli_query($link, $querystring);
		$personInfo2 = array();
		while($queryresponse = mysqli_fetch_assoc($result))
		{
			$personInfo2[] = $queryresponse;
		}
		
		array_push($followerArr,$personInfo2);
	}

	array_push($totalPayload,$person);
	array_push($totalPayload,$followerArr);
	
	echo(json_encode($totalPayload));
	return;
?>
