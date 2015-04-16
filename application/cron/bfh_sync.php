<?php

require 'lib.php';

global $pdo;

if (dbConnect()) {

	try {

		$next_player = $pdo->query("SELECT value FROM crontab WHERE name = 'bfh_next_player'")->fetch(); 
		$params = $pdo->query("SELECT member_id, battlelog_id FROM member WHERE id = {$next_player['value']} AND status_id = 1")->fetch(); 

		if (empty($params)) {

			$pdo->prepare("UPDATE crontab SET value = {$next_player['value']}+1 WHERE name = 'bfh_next_player'")->execute();

		} else {

			$reports = parse_battlelog_reports($params['battlelog_id'], 'bfh');
			newActivity($reports, "bfh", $params['member_id'], $next_player['value']);
			$pdo->prepare("UPDATE crontab SET value = {$next_player['value']}+1 WHERE name = 'bfh_next_player'")->execute(); 

		}

	} catch (PDOException $e) {

		echo $e->getMessage();

	}

}