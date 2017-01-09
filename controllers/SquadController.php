<?php

class SquadController
{

    public static function _doCreateSquad()
    {
        $params = [
            'game_id' => $_POST['division_id'],
            'platoon_id' => $_POST['platoon_id'],
            'leader_id' => $_POST['leader_id']
        ];
        Squad::create($params);

        // update member position, squad id
        if ($params['leader_id']) {
            $params = ['id' => $params['leader_id'], 'position_id' => 5, 'squad_id' => 0];
            Member::modify($params);
        }
    }

    public static function _doModifySquad()
    {

        $params = ['id' => $_POST['squad_id'], 'leader_id' => $_POST['leader_id']];
        Squad::modify($params);

        // update member position, squad id
        if ($params['leader_id']) {
            $params = ['id' => $params['leader_id'], 'position_id' => 5, 'squad_id' => 0];
            Member::modify($params);
        }

    }

    public static function _doRemoveSquad($id)
    {
        $user = User::find(intval($_SESSION['userid']));

        if ($user->role > 0) {
            if (Squad::delete($id)) {
                $data = array('success' => true, 'message' => "Removed!");
            } else {
                $data = array('success' => false, 'message' => "Unit does not exist!");
            }
        } else {
            $data = array('success' => false, 'message' => "You do not have access to perform this function");
        }

        echo Flight::json($data);
    }


    public static function _createSquad()
    {
        Flight::render('modals/create_squad');
    }

    public static function _modifySquad()
    {
        Flight::render('modals/modify_squad');
    }

}
