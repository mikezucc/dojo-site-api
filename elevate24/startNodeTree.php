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
	
	$currdate = date('Y-m-d H:i:s');
	
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
	
	//$length = 44;
	//$messagehash = '';
	//$count = strlen($charset);
	//while ($length--) {
	//	$messagehash .= $charset[mt_rand(0, $count-1)];
	//}
	
	$querystring = "insert into nodes (nodehash, username, made) values ('$nodehash', '$username','$currdate');";
	$result = mysqli_query($link, $querystring);
	
	$dojounlim = $json_obj['dojos'];
	$dojos = explode(",",$dojounlim);
	$numOfDojos = count($dojos);
	$lastdojohash = '';
	for ($i=0;$i<($numOfDojos-1);$i++)
	{
		$dojohash = $dojos[$i];
		$querystring = "insert into nojo (nodehash, dojohash, username, connected) values ('$nodehash', '$dojohash', '$username','$currdate');";
		$result = mysqli_query($link, $querystring);
		if ($dojohash === '')
		{
		
		}
		else
		{
			$lastdojohash = $dojohash;
		}
	}
	
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
			$querystring = "insert into elements (elemhash, nodehash, type, username, made, mediakey, likes, trashes, repeats, width, height) values ('$elemhash', '$nodehash', '$type', '$username','$currdate', '$mediakey', '0', '0', '0', '320', '568');";
			$result = mysqli_query($link, $querystring);
		}
	}
	
	$querystring = "select * from sheep where (leader='$username');";
	$result = mysqli_query($link, $querystring);
	$rosterpeeps = array();
	while($row = mysqli_fetch_assoc($result)) {
		$rosterpeeps[] = $row;
	}
	
	$querystring = "select * from users where (username='$username');";
	$result = mysqli_query($link, $querystring);
	$OPinfo = array();
	while($row = mysqli_fetch_assoc($result)) {
		$OPinfo[] = $row; 
	}
	$OPName = $OPinfo[0]['fullname'];
	
	$querystring = "select * from dojos where (dojohash='$lastdojohash');";
	$result = mysqli_query($link, $querystring);
	$dojoInfo = array();
	while($row = mysqli_fetch_assoc($result)) {
		$dojoInfo[] = $row; 
	}
	$dojoName = $dojoInfo[0]['dojo'];
	$numOfDojos = $numOfDojos - 1;
	for ($k = 0; $k < count($rosterpeeps); $k++)
	{
		$eachemail = $rosterpeeps[$k]['follower'];
		if ($eachemail === $OPName)
		{
		
		}
		else
		{
			if ($numOfDojos == 0)
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'post', ' posted to $dojoName', 'no', '$lastdojohash', '$nodehash');";
				$result = mysqli_query($link, $querystring);
			}
			else
			{
				$querystring = "insert into noti420  (made, forwhom, instigator, type, payload, seen, target, subject) values ( '$currdate', '$eachemail', '$username', 'post', ' posted to $dojoName and $numOfDojos other dojos', 'no', '$lastdojohash', '$nodehash');";
				$result = mysqli_query($link, $querystring);
			}
		}
	}
	
	echo(json_encode("success"));
	mysqli_close($link);	
?>