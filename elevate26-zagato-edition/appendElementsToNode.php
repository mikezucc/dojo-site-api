<?php 
	//error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 
	
	$nodehash = $json_obj['nodehash']; 
	$nodehash = mysqli_real_escape_string($link,$nodehash);
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
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$posterInfo = array();
	while($row = mysqli_fetch_assoc($result)) {
		$posterInfo[] = $row; 
	}
	$posterName = $posterInfo[0]['fullname'];

	$currdate = date('Y-m-d H:i:s');
	$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$querystring = "update nojo set connected='$currdate' where nodehash='$nodehash';";
	$result = mysqli_query($link, $querystring);
	
	//$length = 44;
	//$messagehash = '';
	//$count = strlen($charset);
	//while ($length--) {
	//	$messagehash .= $charset[mt_rand(0, $count-1)];
	//}
	
	$elemunlim = $json_obj['elemkeys'];
	$elems = explode(",",$elemunlim);
	$numOfElems = count($elems);
	for ($i=0;$i<($numOfElems-1);$i++)
	{
		// element objects are just added as whole fuckin values to list of keys specified by elemkeys
		$anElem =  $json_obj[$elems[$i]];
		
		$type = $anElem['type'];
		$type = mysqli_real_escape_string($link,$type);
	
		$text = "";
		if ($type === "text")
		{
			$text = $anElem['text'];
			$text = mysqli_real_escape_string($link,$text);
		}
		$mediakey = "";
		if ($type === "image" || $type === "movie")
		{
			$mediakey = $anElem['key'];
			$mediakey = mysqli_real_escape_string($link,$mediakey);
		}
		
		// generate 
		$length = 44;
		$elemhash = '';
		$count = strlen($charset);
		while ($length--) {
			$elemhash .= $charset[mt_rand(0, $count-1)];
		}
		
		if ($type === 'text')
		{
			$querystring = "insert into elements (elemhash, nodehash, type, username, made, mediakey, likes, trashes, repeats, width, height) values ('$elemhash', '$nodehash', '$type', '$username','$currdate', '', '0', '0' ,'0', '320', '568');";
			$result = mysqli_query($link, $querystring);
			
			$querystring = "insert into textelems (elemhash, textbody, updated) values ('$elemhash', '$text','$currdate');";
			$result = mysqli_query($link, $querystring);
		}
		else
		{
			$querystring = "insert into elements (elemhash, nodehash, type, username, made, mediakey, likes, trashes, repeats, width, height) values ('$elemhash', '$nodehash', '$type', '$username','$currdate', '$mediakey', '0', '0' ,'0', '320', '568');";
			$result = mysqli_query($link, $querystring);
		}
	}
	echo(json_encode("success"));
	mysqli_close($link);	
?>