<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>TS Online</title>
</head>
<style type="text/css">
	body {
		font-family: Tahoma, sans-serif;
		text-align: center;
	}
	header {
		text-align: center;
	}

	.user {
		margin: auto;
		margin-bottom: 0.5em;
		display: block;
		width: 300px;
		border-style: solid;
		border-width: 2px;
		border-color: green;
		border-radius: 5px;
	}

	.meta {
		display: inline;
		font-size: 0.85em;
	}

	.meta-right {
		float: right;
		margin-right: 20px;
	}

	.meta-left {
		float:  left;
		margin-left: 20px;
	}

	.main {
		width:  80%;
		margin: auto;
	}
</style>
<body>

<header>
	<h1>TS Online users</h1>
</header>

<div class="main">

<?php

$tsServerUrl = "http://192.168.3.18:10080";		//That's your TS Server's address, WebQuery is always on port 10080

$usersOnline = array();
if (isset($_COOKIE['lastReq'])) {
	if (time() - $_COOKIE['lastReq'] < 10) {
		$nextUpdateTime = new DateTime();
		date_timestamp_set($nextUpdateTime, $_COOKIE['lastReq']+10);
		date_timezone_set($nextUpdateTime, new DateTimeZone('+0300'));		//Set your timezone
		echo '<div class="meta meta-right">Следующее обновление в '.$nextUpdateTime->format('H:i:s').'</div>';
		$usersOnline = json_decode($_COOKIE['cachedUsers'], true);
	} else $usersOnline = checkUsersOnline();
} else $usersOnline = checkUsersOnline();

function getUserLastConnected($clid) {

	$con = curl_init();
	curl_setopt($con, CURLOPT_URL, $tsServerUrl."/1/clientinfo?clid=".$clid);
	$header = array("Content-Type: application/json", "x-api-key: BACX16B-iK5U-uE2LyppWLwLFtikvkcy1haCkK-");
	curl_setopt($con, CURLOPT_HTTPHEADER, $header);
	curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);
	$res = curl_exec($con);
	curl_close($con);
	if (curl_getinfo($con, CURLINFO_HTTP_CODE) == 200) {
		
		$json = json_decode($res);

		$date1 = new DateTime();
		$date2 = new DateTime();

		date_timestamp_set($date1, time()); //current time
		date_timestamp_set($date2, $json->body[0]->client_lastconnected); //last login time

		$int = $date1->diff($date2);
		return $int->format('Connected %d days %h hours %i minutes ago');
	} else return "";

}

function checkUsersOnline() {

	$con = curl_init();
	curl_setopt($con, CURLOPT_URL, $tsServerUrl."/1/clientlist");
	$header = array("Content-Type: application/json", "x-api-key: BACX16B-iK5U-uE2LyppWLwLFtikvkcy1haCkK-");
	curl_setopt($con, CURLOPT_HTTPHEADER, $header);
	curl_setopt ($con, CURLOPT_VERBOSE, TRUE);
	curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);

	$res = curl_exec($con);
	$code = curl_getinfo($con, CURLINFO_HTTP_CODE);
	$usersArray = array();

	if ($code == 200) {
		$jsonData = json_decode($res);

		foreach ($jsonData->body as $user => $value) {
			if ($value->client_type != 1) {
				$usersArray[$value->client_nickname] = getUserLastConnected($value->clid);
			}
		}
		setcookie('cachedUsers', json_encode($usersArray), time()+60*60*24*30);
	} else {
		echo curl_error($con);
	}

	curl_close($con);

	setcookie('lastReq', time(), time()+60*60*24*30, '/');

	return $usersArray;
}







$usersCount = 0;
$usersCount = count($usersOnline);
echo '<div class="meta meta-left "style="margin-bottom: 1em">Пользователей на сервере: '. $usersCount .'</div>';
echo '<hr style="width: 100%; margin-bottom: 1em;">';
foreach ($usersOnline as $key => $value) {
	echo '<div class="user"><p>'.$key.'</p><p style="font-size: 0.7em; color: grey;">'.$value.'</p></div>';
}





?>

</div>
</body>
</html>