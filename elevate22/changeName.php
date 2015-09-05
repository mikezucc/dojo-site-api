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
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);	
	$notiBOY = array();
	while($row = mysqli_fetch_assoc($result)) {
		$notiBOY[] = $row;
	}
	$notiName = $notiBOY[0]['fullname'];

	$fullname = $json_obj['fullname'];
	$fullname = mysqli_real_escape_string($link,$fullname);
	
	//changed the "dojos" table eventually
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$originalInfo = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$originalInfo[] = $queryresponse;	
	}
	
	$querystring = "update users set fullname='$fullname' where (username='$username');";
	$result = mysqli_query($link, $querystring);
	echo ($result);
	
	$salt1 = $json_obj['salt1'];
	//$salt1 = mysqli_real_escape_string($link,$salt1);
	
	$querystring = "update salts set salt1='$salt1' where (username='$username');";
	$result = mysqli_query($link, $querystring);

	$querystring = "select * from sheep where (leader='$username') order by made desc;"; 
	$result = mysqli_query($link, $querystring);
	$followerStatusArr = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$followerStatusArr[] = $queryresponse;
	}
	
	for ($k=0;$k<count($followerStatusArr);$k++)
	{
		$followerEmail = $followerStatusArr[$k]['follower'];

		$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$followerEmail', '$username', 'followyou', ' changed their name', 'no', '', '');";
		$result = mysqli_query($link, $querystring);
	}
	
?>
