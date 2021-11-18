<?php 
switch ($_SERVER['REQUEST_METHOD']) {
	case "GET":
		$url = "";   //This is the URL of the server on which you host your stats.php file
		header("Location: {$url}/stats.php");
		break;

	case "POST":
		checkPost();
		break;

	default:
		echo ("Method not supported");
		break;
}

function checkPost() {
	$usersOnline = array();

	if (!file_exists(getcwd().'\conf\tsonline.conf')) {
		file_put_contents(getcwd().'\conf\tsonline.conf', "");
	}

	$fileData = json_decode(file_get_contents(getcwd().'\conf\tsonline.conf'), true);
	$time = $fileData['lastReq'];

	$nextUpdateTime = new DateTime();
	if ($time != "") {
		if (time() - intval($time) < 10) {
			date_timestamp_set($nextUpdateTime, intval($time) + 10);
			date_timezone_set($nextUpdateTime, new DateTimeZone('+0300'));
			echo $nextUpdateTime->format('H:i:s');
			$usersOnline = $fileData['cachedUsers'];
		} else $usersOnline = checkUsersOnline();
	} else $usersOnline = checkUsersOnline();


	$responseData = array('nextUpdateTime' => $nextUpdateTime->getTimestamp(), 'users' => $usersOnline);
	header('Content-Type": application/json; charset=utf-8');
	echo json_encode($responseData);


}


function getUserLastConnected($clid) {

	$con = curl_init();
	curl_setopt($con, CURLOPT_URL, "http://192.168.3.18:10080/1/clientinfo?clid=".$clid);
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
		if (intval($int->format('%d')) <= 1) {
			return $int->format('Подключён %h:%i:%s назад');
		} else return "Подключался >1 дня назад";
	} else return "";

}


function checkUsersOnline() {

	$con = curl_init();
	curl_setopt($con, CURLOPT_URL, "http://192.168.3.18:10080/1/clientlist");
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

		
	} else {
		echo curl_error($con);
	}

	curl_close($con);

	$dataToWrite = array('lastReq' => time(), 'cachedUsers' => $usersArray);
	file_put_contents(getcwd().'\conf\tsonline.conf', json_encode($dataToWrite));

	return $usersArray;
}


?>