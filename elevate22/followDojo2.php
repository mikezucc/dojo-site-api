<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$dojohash = $json_obj['dojohash'];
	$dojohash = mysqli_real_escape_string($link,$dojohash);
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
	
	$querystring = "select status from roster2 where (username='$username' and  dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$arr = array();
	while($row = mysqli_fetch_assoc($result)) 
	{
		$arr[] = $row; 
	}
	
	$totalPayload = array();
	
	if (empty($arr))
	{
		$querystring = "insert into roster2 (dojohash, username, status, made) values ('$dojohash','$username','following','$currdate');"; 
		$result = mysqli_query($link, $querystring);
		if($result == true)
		{
			array_push($totalPayload,"following");
			
			
			$querystring = "select * from users where (username='$username');";
			$result = mysqli_query($link, $querystring);	
			$notiBOY = array();
			while($row = mysqli_fetch_assoc($result)) {
				$notiBOY[] = $row; 
			}
			$notiName = $notiBOY[0]['fullname'];
	
			$querystring = "select * from ones where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);	
			$creatorBoy = array();
			while($row = mysqli_fetch_assoc($result)) {
				$creatorBoy[] = $row; 
			}
			$creatorEmail = $creatorBoy[0]['username'];
	
			$querystring = "select * from dojos where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);	
			$dojoBoy = array();
			while($row = mysqli_fetch_assoc($result)) {
				$dojoBoy[] = $row; 
			}
			$dojoName = $dojoBoy[0]['dojo'];
			/*
			$querystring = "select * from users where (email='$email');";
			$result = mysqli_query($link, $querystring);	
			$creatorUser = array();
			while($row = mysqli_fetch_assoc($result)) {
				$creatorUser[] = $row; 
			}
			$creatorName = $creatorUser[0]['fullname'];
			*/
	
			$querystring = "select * from roster2 where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);	
			$dojoPeeps = array();
			while($row = mysqli_fetch_assoc($result)) {
				$dojoPeeps[] = $row; 
			}
	
			for ($j = 0;$j<count($dojoPeeps);$j++)
			{
				$dojoUN = $dojoPeeps[$j]['username'];
				$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$dojoUN', '$username', 'followdojo', ' followed $dojoName', 'no', '', '$dojohash');";
				$result = mysqli_query($link, $querystring);
			}
		}
		else
		{
			array_push($totalPayload,"unable");
		}
	}
	else
	{
		$querystring = "delete from roster2 where (dojohash='$dojohash' and username='$username');"; 
		$result = mysqli_query($link, $querystring);
		if($result == true)
		{
			array_push($totalPayload,"unfollow");
		}
		else
		{
			array_push($totalPayload,"unable");
		}
	}
	
	echo(json_encode($totalPayload));
	
	
?>
