<?php

/**
 * Database
 * provides interface for database manipulation
 */
class Database {
	
	public $pdo;
	private $lastQuery;
	private $lastParams;
	private $lastStatement;
	private $config;
	
	/**
	 * Connects to database with given config
	 */
	public function connect($config){
		$this->config = $config;

		$dsn = 'mysql:host='.$this->config['address'].';dbname='.$this->config['database'].';charset=utf8mb4';

		try {
			$this->pdo = new PDO(
				$dsn,
				$this->config['username'],
				$this->config['password'],
				array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_EMULATE_PREPARES => false
				)
			);
		} catch (PDOException $e) {
			error_log('Database connection error: '.$e->getMessage());
			die('Database connection failed.');
		}
	}

	/*
	 * Returns number of affected rows
	 */
	public function affected(){
		return $this->lastStatement ? $this->lastStatement->rowCount() : 0;
	}

	/**
	 * Dies and prints all relevant error information
	 */
	private function handleError($e) {
		error_log('Database error: '.$e->getMessage());
		die('A database error occurred.');
	}
	
	/**
	 * Get count of rows matched by last query
	 * Works only with standard SELECT FROM queries!!!
	 */
	public function getCount(){
		$q = $this->lastQuery;
		$params = $this->lastParams;
		if (!$q) {
			return 0;
		}

		// Prepare query: replace select fields with count, and remove LIMIT
		$q = preg_replace('/SELECT (.*?) FROM/i','SELECT COUNT(*) AS count FROM',$q);
		$q = preg_replace('/LIMIT (.*)/i','',$q);
		$ret = $this->query($q, $params);
		return isset($ret[0]['count']) ? (int) $ret[0]['count'] : 0;
	}
	
	/**
	 * Executes given query and returns associative array of results, or true if no rows were returned by DB
	 */
	public function query($q, $params = array()){
		$this->lastQuery = $q;
		$this->lastParams = $params;

		try {
			$stmt = $this->pdo->prepare($q);
			$stmt->execute($params);
			$this->lastStatement = $stmt;

			if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $q)) {
				return $stmt->fetchAll();
			}

			return true;
		} catch (Throwable $e) {
			$this->handleError($e);
		}
	}

	public function lastInsertId(){
		return (int) $this->pdo->lastInsertId();
	}
}

return new Database();
