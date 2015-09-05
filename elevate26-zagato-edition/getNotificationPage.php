<?php
	error_reporting(0);
	$link = mysqli_connect("localhost", "root", "dosh1", "dojotest");
	$url_body = file_get_contents('php://input');

	$json_obj = json_decode($url_body, true); 

	$username = $json_obj['username']; 
	$username = mysqli_real_escape_string($link,$username);
	$lati = $json_obj['lati'];
	$longi = $json_obj['longi'];
	
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
	
	$querystring = "select * from noti420 where (forwhom='$username' and seen='no') order by made desc;";
	$result = mysqli_query($link, $querystring);
	$newResults = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
	  	$newResults[] = $queryresponse;
	}
	$numberOfNewResults = count($newResults);
	
	$querystring = "select * from noti420 where (forwhom='$username') order by made desc limit 40;";
	$result = mysqli_query($link, $querystring);
	$notiResults = array();
	while($queryresponse = mysqli_fetch_assoc($result))
	{
	  	$notiResults[] = $queryresponse;
	}
	
	$currdate = date('Y-m-d H:i:s');
	$currdateFormat = new DateTime($currdate);
	
	$fuckingBullcunt = array();
	
	for ($k=0;$k<count($notiResults);$k++)
	{
		$interim = array();
		$madeTime = $notiResults[$k]['made'];
		$madeTimeFormat = new DateTime($madeTime);
		

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
		
		$type = $notiResults[$k]['type'];
		$flagForNoti = $notiResults[$k]['seen'];
		
		if ($flagForNoti === 'no')
		{
			array_push($fuckingBullcunt,$notiResults[$k]);
		}
		
		$instigator = $notiResults[$k]['instigator'];
		if ($type === 'bio')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'comment')
		{
			//continue;
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$posthash = $notiResults[$k]['target'];
			$querystring = "select * from posts where (posthash='$posthash') order by posttime desc;";
			$result = mysqli_query($link, $querystring);
			$postInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$postInfo[] = $queryresponse;
			}
			/*if (empty($postInfo))
			{
				continue;
			}*/
			$dojohash = $postInfo[0]['dojohash'];
			/*
			if (count($postInfo)==1)
			{
				$dojohash = $postInfo[0]['dojohash'];
			}
			else
			{
				
			}*/
				
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$messagehash = $notiResults[$k]['subject'];
			$querystring = "select * from commentboard where (messagehash='$messagehash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$messageInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$messageInfo[] = $queryresponse;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $postInfo);
			array_push($interim, $messageInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'message')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$dojohash = $notiResults[$k]['target'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$messagehash = $notiResults[$k]['subject'];
			$querystring = "select * from messageboard where (messagehash='$messagehash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$messageInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$messageInfo[] = $queryresponse;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $messageInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'create')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$dojohash = $notiResults[$k]['subject'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$querystring = "select * from roster2 where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$followers = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$followers[] = $queryresponse;
			}
			$followersCount = count($followers);
			
			$querystring = "select * from posts where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$posts = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$posts[] = $queryresponse;
			}
			$postCount = count($posts);
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $followersCount);
			array_push($interim, $postCount);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'post')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$dojohash = $notiResults[$k]['target'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$nodehash = $notiResults[$k]['subject'];
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$postInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$postInfo[] = $queryresponse;
			}
			if (empty($postInfo))
			{
			 continue;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $postInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'like')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$dojohash = $notiResults[$k]['target'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$nodehash = $notiResults[$k]['subject'];
			$querystring = "select * from nojo where (nodehash='$nodehash') limit 1;";
			$result = mysqli_query($link, $querystring);
			$postInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$postInfo[] = $queryresponse;
			}
			if (empty($postInfo))
			{
			 continue;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $postInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'followyou')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$querystring = "select * from posts where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$posts = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$posts[] = $queryresponse;
			}
			$postsCount = count($posts);
			
			$querystring = "select * from sheep where (leader='$instigator');";
			$result = mysqli_query($link, $querystring);
			$followers = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$followers[] = $queryresponse;
			}
			$followersCount = count($followers);
			$voteUpArr = array();
			$voteDownArr = array();
			for ($i=0;$i<count($posts);$i++)
			{
				$posthash = $posts[$i]['posthash'];
				$querystring = "select * from voted where (hash like '%$posthash%' and vote=1);";
				$result = mysqli_query($link, $querystring);
				//$voteUpArr = array();
				while($row = mysqli_fetch_assoc($result)) {
					$voteUpArr[] = $row; 
				}
		
				$querystring = "select * from voted where (hash like '%$posthash%' and vote=0);";
				$result = mysqli_query($link, $querystring);
				//$voteDownArr = array();
				while($row = mysqli_fetch_assoc($result)) {
					$voteDownArr[] = $row; 
				}
			}
		
			$totalUpvote = count($voteUpArr);
			$totalDownvote = count($voteDownArr);
			$highestVote = $totalUpvote - $totalDownvote;
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $postsCount);
			array_push($interim, $highestVote);
			array_push($interim, $followersCount);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'repost')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$posthash = $notiResults[$k]['subject'];
			$querystring = "select * from posts where (posthash='$posthash') order by posttime desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$postInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$postInfo[] = $queryresponse;
			}
			if (empty($postInfo))
			{
			 continue;
			}
			
			$dojohash = $postInfo[0]['dojohash'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $postInfo);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		if ($type === 'followdojo')
		{
			$querystring = "select * from users where (username='$instigator');";
			$result = mysqli_query($link, $querystring);
			$userInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$userInfo[] = $queryresponse;
			}
			if (empty($userInfo))
			{
			 continue;
			}
			
			$dojohash = $notiResults[$k]['subject'];
			$querystring = "select * from dojos where (dojohash='$dojohash') order by made desc limit 1;";
			$result = mysqli_query($link, $querystring);
			$dojoInfo = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$dojoInfo[] = $queryresponse;
			}
			if (empty($dojoInfo))
			{
			 continue;
			}
			
			$querystring = "select * from sheep where (leader='$instigator');";
			$result = mysqli_query($link, $querystring);
			$followers = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$followers[] = $queryresponse;
			}
			$followersCount = count($followers);
			
			$querystring = "select * from posts where (dojohash='$dojohash');";
			$result = mysqli_query($link, $querystring);
			$posts = array();
			while($queryresponse = mysqli_fetch_assoc($result))
			{
				$posts[] = $queryresponse;
			}
			$postCount = count($posts);
			
			array_push($interim, $notiResults[$k]);
			array_push($interim, $userInfo);
			array_push($interim, $dojoInfo);
			array_push($interim, $followersCount);
			array_push($interim, $postCount);
			array_push($interim, $strang);
			array_push($totalPayload, $interim);
			continue;
		}
		
	}
	
	$querystring = "update noti2 set seen='yes' where (forwhom='$username');";
	$result = mysqli_query($link, $querystring);
	
	$ultraLoad = array();
	array_push($ultraLoad, $totalPayload);
	if (empty($numberOfNewResults))
	{
		$swero = 0;
		array_push($ultraLoad, $swero);
	}
	else
	{
		array_push($ultraLoad, $numberOfNewResults);
	}
	$finalFinalArray = array();
	
	array_push($finalFinalArray, $ultraLoad);
	array_push($finalFinalArray, $fuckingBullcunt);
	echo(json_encode($finalFinalArray));
	mysqli_close($link);
?>
