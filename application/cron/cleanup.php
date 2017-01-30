<?php

/**
 * cleanup cron
 */

require 'lib.php';

if (dbConnect()) {

	try {

		// removes flags for members who have already been processed out
		$pdo->prepare("DELETE FROM inactive_flagged WHERE member_id IN (SELECT member_id FROM member WHERE status_id = 4)")->execute();

		// clean up members who are no longer in AOD
        $pdo->prepare("UPDATE member SET platoon_id = 0, squad_id =0, position_id = 6 WHERE status_id = 4");

		// cleans up flagged members who have posted since being flagged (for inactivity)
		$pdo->prepare("DELETE FROM inactive_flagged WHERE inactive_flagged.member_id IN (SELECT member_id FROM member WHERE member.last_activity BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW())")->execute();

		// cleans up member games, deleting entries for non-members
		$pdo->prepare("DELETE FROM member_games WHERE member_id NOT IN (SELECT member.id FROM member WHERE status_id IN (1,999,3))")->execute();

		// clean up part time for non members
        $pdo->prepare('DELETE FROM part_timers WHERE member_id NOT IN (SELECT member.member_id FROM member WHERE status_id IN (1,999,3))')->execute();

	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}

}
