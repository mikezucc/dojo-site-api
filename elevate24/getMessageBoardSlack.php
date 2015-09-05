<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$dojohash = $json_obj['dojohash']; 
	$dojohash = mysqli_real_escape_string($link,$dojohash);
	
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
	$currdateFormat = new DateTime($currdate);
	
	// set all these messages to seen
	$querystring = "select * from messagest where (dojohash='$dojohash' and username='$username');";
	$result = mysqli_query($link, $querystring);
	$arr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$arr[] = $row; 
	}
	if (empty($arr[0]))
	{
		// user not have entry in messagest
		$querystring = "insert into messagest (username, dojohash, seen, changed) values ('$username', '$dojohash', 'yes', '$currdate');";
		$result = mysqli_query($link, $querystring);
		if ($result)
		{
			//echo(json_encode("inserted for '$specificEmail'"));
		}
		else
		{
			//echo(json_encode("failed insert for '$specificEmail'"));
		}
	}
	else
	{
		// user is there
		$querystring = "update messagest set seen='yes' where (username='$username' and dojohash='$dojohash');";
		$result = mysqli_query($link, $querystring);
		if ($result)
		{
			//echo(json_encode("updated for '$specificEmail'"));
		}
		else
		{
			//echo(json_encode("failed update for '$specificEmail'"));
		}
	}
	
	$querystring = "select * from messageboard where (dojohash='$dojohash');";
	$result = mysqli_query($link, $querystring);
	$flipped = array();
	while($row = mysqli_fetch_assoc($result)) {
		$flipped[] = $row; 
	}
	$messages = array();
	for ($i=0;$i<count($flipped);$i++)
	{
		$intermediate = array();
		$message = $flipped[$i];
		$email = $message['username'];
		$email = mysqli_real_escape_string($link,$email);
		$querystring = "select * from users where (username='$email');";
		$result = mysqli_query($link, $querystring);
		$userdata = array();
		while($row = mysqli_fetch_assoc($result)) {
			$userdata[] = $row; 
		}
		$interval = "";
		
		$madeTime = $message['made'];
		$madeTimeFormat = new DateTime($madeTime);
		$currdateFormat = new DateTime($currdate);

		  $month1 = $madeTimeFormat->format('m'); 
		  $month2 = $currdateFormat->format('m'); 
		  $month = abs($month2 - $month1);

		  $strang = "never ago";
		  if($month > 0) 
		  { 
				if ($month == 1)
				{
					$strang = "1mo ago";
				}
				else
				{
					$strang = $month . "mo ago";
				}
		  } 
		  else
		  {
			  $day1 = $madeTimeFormat->format('d'); 
			  $day2 = $currdateFormat->format('d'); 
			  $day = abs($day2 - $day1);
		  
				if($day > 0) 
				{ 
					if ($day == 1)
					{
						$strang = "1d ago";
					}
					else
					{
						$strang = $day . "d ago"; 
					}
					if ($day > 5)
					{
						$strang = "1w ago";
					}
				}
				else
				{
					$h1 = $madeTimeFormat->format('h'); 
				  $h2 = $currdateFormat->format('h'); 
				  $H = abs($h2 - $h1);
			  
					if($H > 0)
					{
						if ($H == 1)
						{
							$strang = "1h ago";
						}
						else
						{
							$strang = $H . "hrs ago";
						}
					}
					else
					{
				
					  $i1 = $madeTimeFormat->format('i'); 
					  $i2 = $currdateFormat->format('i'); 
					  $mins = abs($i2 - $i1);

						if($mins > 0)
						{ 
							if ($mins == 1)
							{
								$strang = "1m ago";
							}
							else
							{
								$strang = $mins . "m ago";
							}
						}
						else
						{
							$strang = "now"; 
						}
					}
				}
		  }
    	
    	$initialMessage = strval($message['message']);
    	$extendedMessage =  $initialMessage . "              ";
    	$message['message'] = $extendedMessage;
		
		array_push($intermediate,$userdata);
		array_push($intermediate,$message);
		array_push($intermediate,$strang);
		array_push($messages,$intermediate);
	}
	
	if (count($flipped) == 0)
	{
		$intermediate = array();
		$querystring = "select * from users where (username='gJ45Yl66Mv51ex61Nj22Ts87');";
		$result = mysqli_query($link, $querystring);
		$userdata = array();
		while($row = mysqli_fetch_assoc($result)) {
			$userdata[] = $row; 
		}
		
		$currdateFormat = new DateTime($currdate);
		$start = new DateTime($currdate);
    	$interval = $start->diff($currdateFormat);
    	
    	$initialMessage = "No messages yet. Be the first to say something!";
    	$extendedMessage = $initialMessage . "              ";
    	$message['message'] = $extendedMessage;
		
		array_push($intermediate,$userdata);
		array_push($intermediate,$message);
		array_push($intermediate,$interval);
		array_push($messages,$intermediate);
	}
	
//	$date = date('Y-m-d H:i:s');
//	echo(json_encode($date));
	
	echo(json_encode($messages));
			mysqli_close($link);
?>
