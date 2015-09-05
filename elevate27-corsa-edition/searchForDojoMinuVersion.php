<?php
	error_reporting(0);
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST');  
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true);
	
	$isFromMinu = false;
	if (empty($url_body))
	{
		$isFromMinu = true;
	}

	$dojo = "";
	$lati = 0;
	$longi = 0;
	$username = "";
	$token = "";
	if ($isFromMinu)
	{
		$dojo = $_GET['dojo']; 
		$dojo = mysqli_real_escape_string($link,$dojo);
		$lati = $_GET['lati'];
		$longi = $_GET['longi'];
	}
	else
	{
	
		if ($username === "erlichbachman")
		{
	
		}
		else
		{
			$username = $json_obj['username']; 
			$username = mysqli_real_escape_string($link,$username);
			$token = $json_obj['token']; 
			$token = mysqli_real_escape_string($link,$token);
			if (empty($username))
			{
				echo("damn yee and your web crawlings!");
				mysqli_close($link);
				return;
			}
	
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
		}
	}
	
	
/*
	$dojo = $_GET['dojo'];
	$dojo = mysqli_real_escape_string($link,$dojo);
	$longi = $_GET['longi'];
	$lati = $_GET['lati'];
	*/

	//change dojos to some sort of dojohash table
	$querystring = "SELECT * FROM dojos WHERE (dojo LIKE '%$dojo%');";
	$result = mysqli_query($link, $querystring);

	while ($unique = mysqli_fetch_assoc($result)) {
		//grab the dojos name and set it
		$results[] = $unique; 
	}
	$finalArr = array();
	if (!empty($results))
	{
		for ($i=0;$i<count($results);$i++)
		{
			$dojoChunkArr = array();
			$dojo = $results[$i];
			$dojohash = $dojo['dojohash'];
			$querystring = "SELECT * FROM roster2 WHERE (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$allroster = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$allroster[] = $unique; 
			}
			
			$querystring = "SELECT * FROM ones WHERE (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$creator = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$creator[] = $unique; 
			}
			
			$creatorhash = $creator[0]['username'];
			$querystring = "SELECT * FROM users WHERE (username='$creatorhash');";
			$result = mysqli_query($link, $querystring);
			$creatorReal = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				$creatorReal[] = $unique; 
			}
			
			
			$querystring = "select * from posts where (dojohash='$dojohash') order by posttime asc limit 1;";
			$result = mysqli_query($link, $querystring);
			$firstPostArr = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$firstPostArr[] = $row; 
			}
			
			$querystring = "select * from posts where (dojohash='$dojohash') order by posttime desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$lastPostArr = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$lastPostArr[] = $row; 
			}
			
			$querystring = "select * from posts where (dojohash='$dojohash') order by posttime desc;";
			$result = mysqli_query($link, $querystring);
			$totalPosts = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$totalPosts[] = $row; 
			}
			
			$postRate = 0;
			if (count($firstPostArr)>0)
			{
				$firstPostTime = new DateTime($firstPostArr[0]['posttime']);
				$lastPostTime = new DateTime($lastPostArr[0]['posttime']);
			
				$distance = $lastPostTime->diff($firstPostTime);
				$distanceStr = $distance->format('%d');
				$intVer = intval($distanceStr);
				if ($intVer != 0)
				{
					$postRate = count($totalPosts)/$intVer;
				}
			}
			
			$querystring = "SELECT * FROM locations WHERE (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$proximityArray = array();
			while ($unique = mysqli_fetch_assoc($result)) {
				//grab the dojos name and set it
				$proximityArray[] = $unique; 
			}
			
			$dojoLongi = $proximityArray[0]['longi'];
			$dojoLati = $proximityArray[0]['lati'];
			
			$querystring = "select * from messageboard where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$totalMessages = array();
			while($row = mysqli_fetch_assoc($result)) 
			{
				$totalMessages[] = $row; 
			}
			
			$messageArr = array();
			for ($k=0;$k<count($totalMessages);$k++)
			{
				$username = $totalMessages[$k]['username'];
				$querystring = "SELECT * FROM users WHERE (username='$username');";
				$result = mysqli_query($link, $querystring);
				$usernamearr = array();
				while ($unique = mysqli_fetch_assoc($result)) {
					$usernamearr[] = $unique; 
				}
				$fullname = $usernamearr[0]['fullname'];
				$profilehash = $usernamearr[0]['profilehash'];
				$messagepayload = $totalMessages[$k]['message'];
				
				$interim = array();
				array_push($interim, $fullname);
				array_push($interim, $profilehash);
				array_push($interim, $messagepayload);
				array_push($messageArr,$interim);
			}
			
			$interim = array();
			array_push($interim, $dojo);
			array_push($interim, $creatorReal);
			array_push($interim, $allroster);
			array_push($interim, $dojoLongi);
			array_push($interim, $dojoLati);
			array_push($interim, count($totalPosts));
			array_push($interim, $totalPosts);
			array_push($interim, $postRate);
			array_push($interim, count($totalMessages));
			array_push($interim, $messageArr);
			array_push($finalArr, $interim);
		}
	}
	
	
	//echo(json_encode($finalArr));
	if ($isFromMinu)
	{
		echo $_GET['callback'] . "(" . json_encode($finalArr)  . ")" ;
	}
	else
	{
		echo json_encode($finalArr);
	}
	
	mysqli_close($link);
?>
