<?php

class ApplicationController
{

    public static function _index()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));
        $notifications = new Notification($user, $member);

        $squad = Squad::find($member->member_id);
        $platoon = Platoon::find($member->platoon_id);
        $squads = Squad::findAll($member->game_id, $member->platoon_id);

        // report stuff
        $recruitData = Report::findAllRecruitsThisMonth();
        $activeMembers = Member::findAllActiveMembers();

        Flight::render('user/main_tools', compact('user', 'tools'), 'main_tools');
        Flight::render('member/personnel', compact('member', 'squad', 'platoon', 'squads'), 'personnel');
        Flight::render('application/divisions', compact('divisions'), 'divisions_list');
        Flight::render('user/notifications', array('notifications' => $notifications->messages), 'notifications_list');
        Flight::render('layouts/home', compact('user', 'member', 'division', 'recruitData', 'activeMembers'), 'content');
        Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions', 'division'));
    }

    public static function _activity($findBy=false)
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));

        if ($findBy) {
            $division = Division::findByName($findBy);
        }

        Flight::render('application/activity', compact('division'), 'content');
        Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions'));
    }

    public static function _help()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));
        $js = 'help';

        Flight::render('application/help', compact('user', 'member', 'division'), 'content');
        Flight::render('layouts/application', compact('js', 'user', 'member', 'tools', 'divisions'));
    }

    public static function _doUsersOnline()
    {
        if (isset($_SESSION['loggedIn'])) {
            $user = User::find(intval($_SESSION['userid']));
            $member = Member::find(intval($_SESSION['memberid']));
            Flight::render('user/online_list', compact('user', 'member'));
        } else {
            Flight::render('user/online_list');
        }
    }

    public static function _doSearch()
    {
        $name = trim($_POST['name']);
        $results = Member::search($name);
        Flight::render('member/search', compact('results'));
    }

    public static function _invalidLogin()
    {
        Flight::render('errors/invalid_login', [], 'content');
        Flight::render('layouts/application');
    }

    public static function _unavailable()
    {
        Flight::render('errors/unavailable', [], 'content');
        Flight::render('errors/main');
    }

    public static function _404()
    {
        Flight::render('errors/404', [], 'content');
        Flight::render('errors/main');
    }

    public static function _error()
    {
        Flight::render('errors/error', [], 'content');
        Flight::render('errors/main');
    }

    public static function _doUpdateAlert()
    {
        $id = $_POST['id'];
        $user = $_POST['user'];
        $params = compact('id', 'user');
        AlertStatus::create($params);
    }

    public static function _doGetPersonaId($player)
    {
        if ( ! empty($player)) {
            return self::getBattlelogId($player);
        } else {
            return "You must provide a player name! Ex. /battlefield/get-persona-id/{player}";
        }
    }

    private function getBattlelogId($battlelogName)
    {
        // check for bf4 entry
        $url = "http://api.bf4stats.com/api/playerInfo?plat=pc&name={$battlelogName}";

        ini_set('default_socket_timeout', 10);

        $headers = get_headers_curl($url);

        if ($headers) {

            if (stripos($headers[0], '40') !== false || stripos($headers[0], '50') !== false) {

                $result = array('error' => true, 'message' => 'Player not found, or BF Stats server down.');

            } else {

                $json = get_bf4db_dump($url);
                $data = json_decode($json);
                $personaId = $data->player->id;
                if ( ! containsNumbers($data->player->id)) {
                    $result = array('error' => true, 'message' => 'Player not found, or BF Stats server down.');
                } else {
                    $result = array('error' => false, 'id' => $personaId);
                }
            }

            return $result;
        }

        return $result = array('error' => true, 'message' => 'Timed out. Probably a 404.');
    }

}
