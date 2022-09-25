<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://www.logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 25 Sep, 2022 12:05PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | Auth Controller Class
// +----------------------------+

class AuthCtrl extends DB {
	private static $tableName = "accounts";

	public static function login(string $username, string $password): array {
		$response['status'] = false;
		$response['message'] = "Login failed";

		$db = (new DB(self::$tableName))
			->where([
				"username" => $username
			])
			->select();
		return $response;
	}
}