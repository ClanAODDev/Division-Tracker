<?php

class MemberHandle extends Application {
	
	public $id;
	public $member_id;
	public $handle_type;

	static $id_field = 'id';
	static $table_name = 'member_handles';

	public static function create($params) {
		$handle = new self();
		foreach ($params as $key=>$value) {
			$handle->$key = $value;
		}
		$handle->save($params);
	}

	public static function modify($params) {
		$handle = new self();
		foreach ($params as $key=>$value) {
			$handle->$key = $value;
		}
		$handle->update($params);
	}

	public static function delete($id) {}

}