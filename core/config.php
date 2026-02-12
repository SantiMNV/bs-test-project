<?php

/**
 * Config
 * provides interface for user's configuration options
 */
class Config {
	
	private $directory;
	public $database;
	
	public function __construct() {
		// Save current directory path
		$this->directory = dirname(__FILE__);

		// Load environment values from project's .env file.
		require_once $this->directory.'/env.php';
		loadEnvFile($this->directory.'/../.env');
		
		// Read user's database config
		require $this->directory.'/../config/database.php';
		$this->database = $database;
	}
	
}

return new Config();
