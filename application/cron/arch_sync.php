<?php

require_once('lib.php');

date_default_timezone_set('America/New_York');

$divisions = getDivisions();

if (count($divisions)) {
    foreach ($divisions as $division) {

        $postResult = getData($division);

        $json = json_decode(utf8_encode($postResult));

        // fetch all existing db members for array comparison
        $query = $pdo->prepare("SELECT member_id, forum_name FROM member WHERE status_id = 1 AND game_id = :gid");
        $query->execute([':gid' => intval($division["id"])]);
        $existingMemberArray = $query->fetchAll();
        $existingMembers = [];

        foreach ($existingMemberArray as $member) {
            $existingMembers[$member['forum_name']] = $member['member_id'];
        }

        if (property_exists($json, 'column_order')
            && count($json->column_order) == 14
            && ($json->column_order[0] == 'userid')
            && ($json->column_order[13] == 'aodstatus')
        ) {

            $currentMembers = [];

            // loop through member records
            foreach ($json->data as $column) {

                $memberid = $column[0];
                $username = str_replace('AOD_', '', $column[1]);
                $joindate = $column[2];
                $lastvisit = $column[3] . " " . $column[4];
                $lastactive = $column[5] . " " . $column[6];
                $lastpost = $column[7] . " " . $column[8];
                $postcount = $column[9];

                // only convert if rank is recruit or above
                $aodrankval = ($column[11] > 2) ? $column[11] - 2 : 1;

                $aoddivision = $division['id'];

                // if you're listed, you're active
                $aodstatus = 1;

                global $pdo;
                $currentMembers[$username] = $memberid;

                if (dbConnect()) {

                    $query = $pdo->prepare(
                        "INSERT INTO member (forum_name, member_id, rank_id, status_id, game_id, join_date, last_forum_login, last_forum_post, forum_posts, last_activity)
						VALUES (:username, :memberid, :rank, :status, :division, :joindate, :last_visit, :last_post, :forum_posts, :last_active)
						ON DUPLICATE KEY UPDATE
						forum_name=:username,
						rank_id=:rank,
						join_date=:joindate,
						status_id=:status,
						game_id=:division,
						last_forum_login=:last_visit,
						last_activity=:last_active,
						last_forum_post=:last_post,
						forum_posts=:forum_posts"
                    );

                    try {
                        $member_being_updated = $pdo->prepare("SELECT rank_id FROM member WHERE member_id = {$memberid} LIMIT 1");
                        $member_being_updated->execute();
                        $member_being_updated = $member_being_updated->fetch();
                        if (is_array($member_being_updated)) {
                            if (array_key_exists('rank_id', $member_being_updated)) {
                                if ($member_being_updated['rank_id'] < $aodrankval) {
                                    // member was promoted, update promotion date
                                    $today = date('Y-m-d H:i:s');
                                    $pdo->prepare("UPDATE member SET last_promotion = '{$today}' WHERE member_id = {$memberid}")->execute();
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        exit;
                    }

                    try {
                        $query->execute(
                            [
                                ':username' => $username,
                                ':memberid' => $memberid,
                                ':rank' => $aodrankval,
                                ':status' => $aodstatus,
                                ':division' => $aoddivision,
                                ':joindate' => $joindate,
                                ':last_visit' => $lastvisit,
                                ':last_active' => $lastactive,
                                ':last_post' => $lastpost,
                                ':forum_posts' => $postcount
                            ]
                        );

                    } catch (PDOException $e) {
                        echo "ERROR: " . $e->getMessage();
                    }
                }
            }

            // select members that need to be removed
            $removals = array_diff($existingMembers, $currentMembers);

            if (count($removals)) {
                $removalIds = implode($removals, ", ");

                try {
                    $query = $pdo->prepare("UPDATE member SET status_id = 4, squad_id = 0, platoon_id = 0  WHERE member_id IN ({$removalIds}) AND game_id = :gid");
                    $query->execute([':gid' => intval($division["id"])]);
                } catch (PDOException $e) {
                    echo "ERROR: " . $e->getMessage();
                }

                echo date('Y-m-d h:i:s A') . " - Updated the following member ids to 'removed': " . $removalIds . "\r\n";
            }

            echo date('Y-m-d h:i:s A') . " - {$division['full_name']} sync done. \r\n";

            try {
                $pdo->prepare("UPDATE crontab SET last_updated = '" . date('Y-m-d H:i:s') . "' WHERE name = 'arch_sync'")->execute();
            } catch (PDOException $e) {
                echo "ERROR: " . $e->getMessage();
            }

        } else {
            echo date('Y-m-d h:i:s A') . " - Error: Column count has changed. Parser needs to be updated.\r\n";
            echo "Failed on {$division['full_name']}\r\n";
            echo $json;
            die;
        }
    }

} else {
    echo "There are no divisions to sync.";
}


/**
 * @param $division
 * @return mixed
 */
function getData($division)
{
    $data_path = "https://www.clanaod.net/forums/aodinfo.php?";

    $authcode = hash('sha256', getToken() . ARCH_PASS);

    $args = http_build_query([
        'type' => 'json',
        'authcode' => $authcode,
        'division' => $division['full_name']
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_path . $args);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $results = curl_exec($ch);
    curl_close($ch);

    return $results;
}

/**
 * @return array
 */
function getToken()
{
    $token_path = "https://www.clanaod.net/forums/aodinfo.php?type=gettoken";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_path);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $token = curl_exec($ch);
    curl_close($ch);

    if ( ! $token) {
        die(date('Y-m-d H:i:s') . 'Failed while requesting token');
    }

    return $token;
}