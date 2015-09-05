<?php
	//error_reporting(0);
	$link = mysqli_connect("address", "user", "pass", "dbname");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$posthash = $json_obj['posthash']; 
	$posthash = mysqli_real_escape_string($link,$posthash);
	
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
	
	$querystring = "select * from posts where (posthash='$posthash') limit 1;";
	$result = mysqli_query($link, $querystring);
	$postArr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$postArr[] = $row; 
	}
	
	$messages = array();
	$ophash = $postArr[0]['username'];
	
	$querystring = "select * from users where (username='$ophash');";
	$result = mysqli_query($link, $querystring);
	$userArr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$userArr[] = $row; 
	}
	
	$initialMessage = strval($postArr[0]['description']);
	$extendedMessage = $initialMessage . "              ";
	$postArr[0]['description'] = $extendedMessage;

	$start = new DateTime($postArr[0]['posttime']);
	$interval = $start->diff($currdateFormat);
	
	$msg['posthash'] = $posthash;
	$msg['message'] = $extendedMessage;
	$msg['messagehash'] = 'dank';
	$msg['made'] = $currdate;
	$msg['username'] = $ophash;

	$intermediate = array();
	array_push($intermediate,$userArr);
	array_push($intermediate,$msg);
	array_push($intermediate,$interval);
	array_push($messages,$intermediate);
	
	
	// set all these messages to seen
	$querystring = "select * from commentest where (posthash='$posthash' and username='$username');";
	$result = mysqli_query($link, $querystring);
	$arr = array();
	while($row = mysqli_fetch_assoc($result)) {
		$arr[] = $row; 
	}
	if (empty($arr[0]))
	{
		// user not have entry in messagest
		$querystring = "insert into commentest (username, posthash, seen, changed) values ('$username', '$posthash', 'yes', '$currdate');";
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
		$querystring = "update commentest set seen='yes' where (username='$username' and posthash='$posthash');";
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
	
	$querystring = "select * from commentboard where (posthash='$posthash') order by made asc;";
	$result = mysqli_query($link, $querystring);
	$flipped = array();
	while($row = mysqli_fetch_assoc($result)) {
		$flipped[] = $row; 
	}
	for ($i=0;$i<count($flipped);$i++)
	{
		$intermediate = array();
		$message = $flipped[$i];
		$username = $message['username'];
		$username = mysqli_real_escape_string($link,$username);
		$querystring = "select * from users where (username='$username');";
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
    	$extendedMessage = $strang . " - " . $initialMessage . "              ";
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
