<?php

class UserAction extends Application {

	public $id;
	public $type_id;
	public $date;
	public $user_id;
	public $target_id;

	static $id_field = 'id';
	static $table = 'user_actions';

	/**
	 * user action types:
	 * ------------------------
	 *  id | description
	 * ------------------------
	 * 	1  |  add a new recruit
	 *  2  |  remove a member
	 *  3  |  update a member
	 *  4  |  flag an inactive member
	 *  5  |  generate new division structure
	 *  6  |  unflag an inactive member
	 *  7  |  Approve an loa
	 *  8  |  Deny an loa
	 *  9  |  Revoke an loa
	 *  10 |  recruit former member
	 *  ----------------------- 
	 */

	public static function create($params) {
		$UserAction = new self();
		foreach ($params as $key=>$value) {
			$UserAction->$key = $value;
		}
		$UserAction->save($params);
		//echo Flight::aod()->last_query;
	}

	public static function findAll() {
		return arrayToObject(Flight::aod()->from(self::$table)
			->limit(10)
			->sortDesc('date')
			->join('actions', array('actions.id' => 'user_actions.type_id'))
			->select(array('date','user_id', 'type_id', 'target_id', 'verbage'))->many());
	}

	public static function humanize($type_id, $target_id, $user_id, $verbage) {

		$user = "<a href='member/{$user_id}'>" . Member::findForumName($user_id) . "</a>";
		$player = "<a href='member/{$target_id}'>" . Member::findForumName($target_id) . "</a>";
		switch ($type_id) {
			case 1:
			$text = "{$user} {$verbage} {$player} into the division";
			break;
			case 2: 
			$text = "{$user} {$verbage} {$player} from the division";
			break;
			case 3: 
			$text = "{$user} {$verbage} {$player}'s profile information";
			break;
			case 4: 
			$text = "{$player} was {$verbage} by {$user}";
			break;
			case 5: 
			$text = "{$user} {$verbage}";
			break;
			case 6: 
			$text = "{$player} was {$verbage} by {$user}";
			break;
			case 7: 
			case 8:
			case 9:
			$text = "{$user} {$verbage} for {$player}";
			break;
			case 10:
			$text = "{$user} {$verbage} former member {$player} back into the division";
			break;
		}
		return $text;
	}

}