<?php

// Load BaseModel and all models from models directory
require dirname(__FILE__).'/base_model.php';
foreach (glob(dirname(__FILE__).'/../models/*.php') as $filename){
	require $filename;
}

/**
 * App
 * provides interface for database manipulation, accessing config and rendering views
 */
class App {
	
	private $directory;
	public $db;
	public $config;
	
	public function __construct(){
		// Save current directory path
		$this->directory = dirname(__FILE__);

		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		
		// Load configuration options
		$this->config = require $this->directory.'/config.php';
			
		// Load database instance and tell it to connect with given config
		$this->db = require $this->directory.'/database.php';
		$this->db->connect($this->config->database);
	}	
	
	/**
	 * Renders given view with given set of variables
	 * 
	 * param $viewfile: path of the view file relative to the views direcotry, without the ending .php
	 * param $vars: array of variables to be accessed insede the views
	 */
	public function renderView($viewfile, $vars = array()) {
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

		// Render array to usable variables
		foreach ($vars as $key => $value) {
			$$key = $value;
		}
		
		// Start capturing of output
		ob_start();
		include './views/'.$viewfile.'.php';
		// Assign output to $content which will be rendered in layout
		$content = ob_get_contents();
		// Stop output capturing
		ob_end_clean();
		// Render $content in layout
		include './views/layout.php';
	}
	
}

return new App();
