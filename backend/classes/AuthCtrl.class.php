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

		$account = (new DB(self::$tableName))
			->where([
				"username" => $username
			])
			->select();
		if (empty($account)) return $response;

		if (password_verify($password, $account->password)) {
			$response['status'] = true;
			$response['message'] = "Login successful";
			$response['data']['login_token'] = self::createSessionToken($account->id);
		}
		return $response;
	}

	public static function register(string $username, string $password): array {
		$response['status'] = false;
		$response['message'] = "Registration failed";

		$account = (new DB(self::$tableName))
			->where([
				"username" => $username
			])
			->select();
		if (!empty($account)) {
			$response['message'] = "Username is already taken";
			return $response;
		}

		$insertID = (new DB(self::$tableName))
			->setColumnsAndValues([
				"username" => $username,
				"password" => password_hash($password, PASSWORD_DEFAULT),
				"date_joined" => date('d-M-Y')
			])
			->insert();

		if (!empty($insertID)) {
			$response['status'] = true;
			$response['message'] = "Registration successful";
			$response['data']['login_token'] = self::createSessionToken($insertID);
		}
		return $response;
	}

	private static function createSessionToken(int $user_id):string {
		$token = bin2hex(random_bytes(12));
		(new DB('account_sessions'))
			->setColumnsAndValues([
				"user_id" => $user_id,
				"token" => $token,
				"time" => time(),
				"expiry" => strtotime('+7 days')
			])
			->insert();
		return $token;
	}
}