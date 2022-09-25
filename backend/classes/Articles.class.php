<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://www.logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 19 Sep, 2022 03:05PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | Articles
// +----------------------------+

class Articles extends DB {
	private static $tableName = 'articles';
	private static $siteurl = 'https://demo.logad.net/react-blog/';

	public static function getRecent() {
		try {
			$articles =  (new DB('articles'))
				->selectAll(20);
			foreach ($articles as &$article) {
				$article->image = self::$siteurl.$article->image;
			}
			return $articles;
		}
		catch (Exception $e) {
			// echo $e->getMessage();
			return [];
		}
	}

	## Get article by id ##
	public static function byID($article_id) {
		$article = (new DB(self::$tableName))
			->where([
				"id" => $article_id
			])
			->select();
		if (!empty($article)) {
			$article->image = self::$siteurl.$article->image;
		}
		return $article;
	}

	## Delete article by id ##
	public static function delete($article_id) {
		return (new DB(self::$tableName))
			->deleteWhere([
				"id" => $article_id
			]);
	}

	public static function store($data) {
		$response['message'] = "Failed to store new article";
		$response['status'] = false;

		if (empty($data->imageUrl)) {
			$response['message'] = "Image url is required";
			return $response;
		}
		$check = self::checkImageUrl($data->imageUrl);
		if ($check['status'] !== true) {
		    $response['message'] = $check['message'];
			return $response;
		}

		$db = new DB(self::$tableName);
		$imagepath = APP_BASE."uploads/images/".date('Y-M')."/";
		if (!file_exists($imagepath)) {
			mkdir($imagepath, 0777, true);
		}
		$filename = $imagepath.time().".png";
		file_put_contents($filename, file_get_contents($data->imageUrl));
		$insertID = $db->setColumnsAndValues([
				"title" => $data->title,
				"author" => $data->author,
				"content" => htmlentities($data->content),
				"image" => str_replace(APP_BASE, '', $filename)
			])
			->insert();
		if (!empty($insertID)) {
			$response['status'] = true;
			$response['message'] = "success";
			$response['data'] = self::byID($insertID);
		} else {
			$response['message'] = $db->getErrors();
		}
		return $response;
	}

	private static function checkImageUrl($url) {
		$response['status'] = false;
		$response['message'] = 'Failed to verify image url';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    // don't download content
	    curl_setopt($ch, CURLOPT_NOBODY, 1);
	    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $result = curl_exec($ch);
	    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	    curl_close($ch);
	    if ($result !== FALSE) {
	    	if (!in_array($contentType, array('image/png','image/jpeg','image/jpg'))) {
	    		$response['message'] = "Invalid image type";
	    	} else {
	    		$response['status'] = true;
	    		$response['message'] = "success";
	    	}
	    }
	    return $response;
	}
}