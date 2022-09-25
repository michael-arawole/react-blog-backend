<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://www.logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 19 Sep, 2022 03:02PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | Database Class
// +----------------------------+

class DB {
	private string $table;
	private string $query;
	private array $values;
	private array $columns;
	private array $errors;

	public function __construct($table) {
		if (empty($table))
			throw new Exception('Database Table is required');
		else
		$this->table = $table;
		return $this;
	}

	## PDO Connection ##
	private function PDOConnection() {
	    require __DIR__ . '/../inc/config.php';
	    try {
	        return new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8', $dbUser, $dbPass);
	    } catch (PDOException $exception) {
	    	throw new Exception('Error connecting to database');
	    }
	}

	## Check query ##
	private function checkQuery($pdo) {
		$validate = $pdo->prepare($this->query);
		if (!$validate) {
			throw new Exception("Query could not be prepared");
		}
		return $validate;
	}

	## Set columns and vaulues ##
	public function setColumnsAndValues($columnsAndValues) {
		if (!is_array($columnsAndValues)) throw new Exception('setColumnsAndValues requires an array as parameter');
		$columns = array();
		$values = array();
		foreach ($columnsAndValues as $column => $value) {
			$columns[] = $column;
			$values[] = $value;
		}
		$this->columns = $columns;
		$this->values = $values;
		return $this;
	}

	## Set errors ##
	private function setError($error) {
		$this->errors = $error;
		return $this;
	}

	## Get errors ##
	public function getErrors() {
		return $this->errors;
	}

	## Set where clause ##
	public function where($conditions) {
		$values = array();
		if (empty($this->columns)) {
			$sql = "SELECT * FROM $this->table WHERE ";
			foreach ($conditions as $param => $value) {
				$sql .= "$param = ? AND ";
				$values[] = $value;
			}
		}
		$this->values = $values;
		$this->query = rtrim($sql, "AND ");
		return $this;
	}

	## Delete where clause ##
	public function deleteWhere($conditions) {
		$values = array();
		$sql = "DELETE FROM $this->table WHERE ";
		foreach ($conditions as $param => $value) {
			$sql .= "$param = ? AND ";
			$values[] = $value;
		}
		// $this->values = $values;
		$this->query = rtrim($sql, "AND ");
		$pdo = $this->PDOConnection();
		$statement = $this->checkQuery($pdo);
		$statement->execute($values);
		return $statement->rowCount();
	}

	## Select single record ##
	public function select() {
		$pdo = $this->PDOConnection();
		$statement = $this->checkQuery($pdo);
		$statement->execute($this->values);
		return $statement->fetch(PDO::FETCH_OBJ);
	}

	## Select all records ##
	public function selectAll($limit = null) {
		$this->query = 'SELECT * FROM '. $this->table. ' ORDER BY id DESC';
		if (!empty($limit)) {
			$this->query .= " LIMIT $limit";
		}
		$pdo = $this->PDOConnection();
		$statement = $this->checkQuery($pdo);
		if (!empty($this->values)) {
			$statement->execute($this->values);
		} else {
			$statement->execute();
		}
		return $statement->fetchAll(PDO::FETCH_OBJ);
	}

	## Insert ##
	public function insert() {
		if (empty($this->values)) throw new Exception('Values are required');
		if (empty($this->columns)) throw new Exception('Columns are required');

		$valueString = rtrim(str_repeat("?, ", count($this->values)), ", ");
		$this->query = 'INSERT INTO '. $this->table .' ('. implode(', ', $this->columns) .') values ('. $valueString .')';
		$pdo = $this->PDOConnection();
		$statement = $this->checkQuery($pdo);
		$statement->execute($this->values);
		if ($statement->rowCount() != 0) {
			return $pdo->lastInsertId();
		} else {
			$this->setError($statement->errorInfo());
		}
		return false;
	}

	## Get result ##
	/*public function getResult() {
		// Check if query is not empty
		if (empty($this->query)) throw new Exception("You can't call getResult directly");

		$pdo = $this->PDOConnection();
		$query = $pdo->prepare($this->query);
		if (!empty($values)) {
			$query->execute($values);
		} else {
			$query->execute();
		}

		if (isset($this->options['selectAll'])) {
			return $query->fetchAll(PDO::FETCH_OBJ);
		} else {
			$query->fetch(PDO::FETCH_OBJ);
		}
	}*/
}