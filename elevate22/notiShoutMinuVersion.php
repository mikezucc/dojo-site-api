<?php
	error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$username = $_GET['username'];
	$token = $_GET['token'];

	$username = mysqli_real_escape_string($link,$username);
	$token = mysqli_real_escape_string($link,$token);

	$querystring = "select * from token where (username='$username' and token='$token');";
	$result = mysqli_query($link, $querystring);
	$tokenInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$tokenInfo[] = $queryresponse;	
	}
	if (empty($tokenInfo))
	{
		return;
	}
	
	$currdate = date('Y-m-d H:i:s');
	
	$querystring = "select * from users;";
	$result = mysqli_query($link, $querystring);
	$allUsers = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$allUsers[] = $queryresponse;	
	}
	for ($i=0;$i<count($allUsers);$i++)
	{
		$eachemail = $allUsers[$i]['username'];
		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', 'VH03yD97um98fQ43KB54EF20', 'post', ' has made an announcement', 'no', '$dojohash', '');";
		$result = mysqli_query($link, $querystring);
	}
	
	
?>
