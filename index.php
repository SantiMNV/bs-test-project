<?php

// Init app instance
$app = require "./core/app.php";

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$formErrors = $_SESSION['form_errors'] ?? array();
$oldInput = $_SESSION['old_input'] ?? array();
unset($_SESSION['form_errors'], $_SESSION['old_input']);

// Get all users from DB, eager load all fields using '*'
$users = User::find($app->db,'*');

// Render view 'views/index.php' and pass users variable there
$app->renderView('index', array(
	'users' => $users,
	'formErrors' => $formErrors,
	'oldInput' => $oldInput,
	'csrfToken' => $_SESSION['csrf_token']
));
