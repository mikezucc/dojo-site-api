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
	
	$currdate = date('Y-m-d H:i:s');
	
	$totalPayload = array();

	$personemail = $json_obj['person'];
	$personemail = mysqli_real_escape_string($link,$personemail);
	
	$querystring = "select * from sheep where (leader='$personemail' and follower='$username') order by made desc;"; 
	$result = mysqli_query($link, $querystring);
	$followerStatusArr = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
		$followerStatusArr[] = $queryresponse;
	}
	
	if (empty($followerStatusArr))
	{
		$querystring = "insert into sheep (leader, follower, status, made) values ('$personemail','$username','following','$currdate');"; 
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
	
			$querystring = "insert into noti2  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$personemail', '$username', 'followyou', ' is following you!', 'no', '', '');";
			$result = mysqli_query($link, $querystring);
		}
		else
		{
			array_push($totalPayload,"unable");
		}
	}
	else
	{
		$querystring = "delete from sheep where (leader='$personemail' and follower='$username');"; 
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
