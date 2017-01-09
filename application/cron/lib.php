<?php

require 'config.php';
require '../uagent.php';

function dbConnect()
{
	global $pdo;
	$conn = '';

	try {
		$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	catch (PDOException $e) {
		if (DEBUG_MODE)
			echo "<div class='alert alert-danger'><i class='fa fa-exclamation-circle'></i><strong>Database connection error</strong>: " . $e->getMessage() . "</div>";
	}

	return true;
}

function getDivisions() {
	global $pdo;
	if (dbConnect()) {
		try {
			$query = "SELECT * FROM divisions";
			$query = $pdo->prepare($query);
			$query->execute();
			$query = $query->fetchAll();
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	return $query;
}


function convertStatus($status) {

	$status = (stristr($status, "LOA")) ? "LOA" : $status;

	switch ($status) {

		case "Active":
		$id = 1;
		break;
		case "On Leave":
		case "Missing in Action":
		case "LOA":
		$id = 3;
		break;
		case "Retired":
		$id = 4;
		break;

	}
	return $id;
}

function bf_newActivity($reports, $game, $member_id, $id) {
	global $pdo;
	if (dbConnect()) {
		foreach ($reports as $report) {
			try {
				$sql = "INSERT IGNORE INTO bf_activity (member_id, server, datetime, hash, game_id, map_name, report_id) VALUES (:member, :serverName, :date, :hash, :game, :map, :report)";

				$pdo->prepare($sql)
				->execute(array(
					':member' => $member_id,
					':serverName' => $report['serverName'],
					':date' => $report['date'],
					':hash' => hash("sha256", $member_id.$report['date']),
					':game' => $game,
					':map' => $report['map'],
					':report' => $report['reportId'])
				);
			} catch (PDOException $e) {
				echo $e->getMessage();
			}
		}
	}
}



function _doBattlelogIdUpdate() {
	$members = array();
	$battlelog_names = objectToArray(Member::find(array('status_id' => 1, 'battlelog_name !%' => 0, 'battlelog_id' => 0)));
	$countNames = count($battlelog_names);
	echo "Fetched battlelog names. ({$countNames})<br /><br />";
	foreach ($battlelog_names as $row) {
		$battlelog_id = Member::getBattlelogId($row['battlelog_name']);
		if (!$battlelog_id['error']) {
			$sql = "UPDATE member SET battlelog_id = {$battlelog_id['id']} WHERE battlelog_name = '{$row['battlelog_name']}'";
			Flight::aod()->sql($sql)->one();
			echo "Added ID {$battlelog_id['id']} to {$row['battlelog_name']}<br />";
		} else {
			echo "ERROR: {$row['battlelog_name']} - {$battlelog_id['message']}<br />";
		}
	}
	echo "done syncing battlelog ids.";
}

function parse_battlelog_reports($personaId, $game) {

	$reports = download_bl_reports($personaId, $game);
	$arrayReports = array();
	$i = 1;

	if (!is_null(($reports))) {
		foreach ($reports as $report) {
			$unix_date = $report->createdAt;
			$date = DateTime::createFromFormat('U', $unix_date)->format('Y-m-d H:i:s');
			if ( strtotime($date) > strtotime('-90 days') ) {
				$arrayReports[$i]['reportId'] = $report->gameReportId;
				$arrayReports[$i]['serverName'] = $report->name;
				$arrayReports[$i]['map'] = $report->map;
				$arrayReports[$i]['date'] = $date;
				$i++;
			}
		}
	}

	return $arrayReports;
}


function download_bl_reports($personaId, $game) {

	$agent = random_uagent();

	$options = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept-language: en\r\n" .
			"Cookie: foo=bar\r\n" .
			"User-Agent: {$agent}\r\n"
			)
		);

	$context = stream_context_create($options);

	switch ($game) {
		case 'bf4':
		$url = "https://battlelog.battlefield.com/bf4/warsawbattlereportspopulate/{$personaId}/2048/1/";
		break;
		case 'bfh':
		$url = "https://battlelog.battlefield.com/bfh/warsawbattlereportspopulate/{$personaId}/8192/1/";
	}

	$json = file_get_contents($url, false, $context);
	$data = json_decode($json);

	$reports = $data->data->gameReports;

	return $reports;
}

function containsNumbers($String){
	return preg_match('/\\d/', $String) > 0;
}


function getBattlelogId($battlelogName) {
	// check for bf4 entry
	$url = "https://api.bf4stats.com/api/playerInfo?plat=pc&name={$battlelogName}";
	ini_set('default_socket_timeout', 10);
	$headers = get_headers_curl($url);
	if ($headers) {
		if (stripos($headers[0], '40') !== false || stripos($headers[0], '50') !== false) {
			$result = array('error' => true, 'message' => 'Player not found, or BF Stats server down.');
		} else {
			$json = get_bf4db_dump($url);
			$data = json_decode($json);
			$personaId = $data->player->id;
			if (!containsNumbers($data->player->id)) {
				$result = array('error' => true, 'message' => 'Player not found, or BF Stats server down.');
			} else {
				$result = array('error' => false, 'id' => $personaId);
			}
		}
		return $result;
	}
	return $result = array('error' => true, 'message' => 'Timed out. Probably a 404.');
}

function get_headers_curl($url)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,            $url);
	curl_setopt($ch, CURLOPT_HEADER,         true);
	curl_setopt($ch, CURLOPT_NOBODY,         true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT,        15);

	$r = curl_exec($ch);
	$r = split("\n", $r);
	return $r;
}

function get_bf4db_dump($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}


/**
 * world of tanks CRON stuff
 */

function download_tanks_profile($account_id, $type) {
	$agent = random_uagent();

	$na_api_key = "16924c431c705523aae25b6f638c54dd";
	$eu_api_key = "d0a293dc77667c9328783d489c8cef73";

	$options = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept-language: en\r\n" .
			"Cookie: foo=bar\r\n" .
			"User-Agent: {$agent}\r\n"
			)
		);

	$context = stream_context_create($options);

	switch ($type) {
		case 7:
		$url = "https://api.worldoftanks.com/2.0/account/info/?application_id={$na_api_key}&account_id={$account_id}";
		break;
		case 8:
		$url = "https://api.worldoftanks.eu/2.0/account/info/?application_id={$eu_api_key}&account_id={$account_id}";
		break;
	}

	$json = file_get_contents($url, false, $context);
	$data = json_decode($json);

	return $data->data->$account_id;
}

function parse_tanks_profile($data) {
	global $pdo;
	if (dbConnect()) {
		try {
			$sql = "INSERT INTO wg_activity (member_id, last_battle_time)
			VALUES (:member_id, :last_battle_time)
			ON DUPLICATE KEY UPDATE
			last_battle_time = :last_battle_time";

			$pdo->prepare($sql)
			->execute(array(
				':member_id' => $data->member_id,
				':last_battle_time' => date("Y-m-d H:i:s", $data->last_battle_time)
				)
			);

		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}
}
