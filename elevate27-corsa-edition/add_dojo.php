<?php
	//connect to sql
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");

	//get the input from the url response from clientside and convert it to RAW json
	$url_body = file_get_contents('php://input');
	$json_obj = json_decode($url_body, true); 
	//recover dojo data
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
	
	$name = $json_obj['name'];
	$name = mysqli_real_escape_string($link,$name);
	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	$lati = $json_obj['lati'];
	$longi = $json_obj['longi'];

	$currdate = date('Y-m-d H:i:s');

	//create dojo
	//dojos must be CHANGED
	$querystring = "insert into dojos (dojo, dojohash, made) values ('$name', '$dojohash','$currdate');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "insert into locations (dojohash, lati, longi, updated) values ('$dojohash', '$lati', '$longi', '$currdate');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "insert into roster2 (dojohash, username, status) values ('$dojohash', '$username', 'following');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "insert into ones (dojohash, username) values ('$dojohash', '$username');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row;
	}
	$notiName = $notiBOY[0]['fullname'];
	
	$querystring = "select * from sheep where (leader='$username' and status='following');";
	$result = mysqli_query($link, $querystring);
	$followerRaw = array();
	while($row = mysqli_fetch_assoc($result)) {
		$followerRaw[] = $row; 
	}
	
	for ($k=0;$k<count($followerRaw);$k++)
	{
		$intermediate = array();
		$followeremail = $followerRaw[$k]['follower'];
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$followeremail', '$username', 'create', ' created $name', 'no', '', '$dojohash');";
		$result = mysqli_query($link, $querystring);
	}
	
	/*
	// add creator of dojo as joined
	$creatoremail = $emailArr[0];
	$creatoremail = mysqli_real_escape_string($link,$creatoremail);
	$querystring = "insert into roster (dojohash, email, status) values ('$dojohash', '$creatoremail', 'joined');";
	$result = mysqli_query($link,$querystring);
	echo(json_encode($result));
	
	// add all invited users as invited
	for ($i = 1;i<count($emailArr);$i++)
	{
		$useremail = $emailArr[$i];
		$useremail = mysqli_real_escape_string($link,$useremail);
		$querystring = "insert into roster (dojohash, email, status) values ('$dojohash', '$useremail', 'invited');";
		$result = mysqli_query($link,$querystring);
		echo(json_encode($result));
	}
	*/
	
	mysqli_query($link,"delete from roster2 where (username='');");

	mysqli_close($link);
?>