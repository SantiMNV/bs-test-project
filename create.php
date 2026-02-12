<?php

$app = require "./core/app.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: index.php', true, 303);
	exit;
}

function redirectWithErrors(array $errors, array $oldInput): void {
	$_SESSION['form_errors'] = $errors;
	$_SESSION['old_input'] = $oldInput;
	header('Location: index.php', true, 303);
	exit;
}

function safeLength(string $value): int {
	return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
	redirectWithErrors(array('The form has expired. Please try again.'), array());
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));

$errors = array();

if ($name === '') {
	$errors[] = 'Name is required.';
} elseif (safeLength($name) > 100) {
	$errors[] = 'Name must be at most 100 characters.';
} elseif (!preg_match('/^[\p{L}\p{M} .\'-]+$/u', $name)) {
	$errors[] = 'Name contains invalid characters.';
}

if ($email === '') {
	$errors[] = 'E-mail is required.';
} elseif (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	$errors[] = 'E-mail address is invalid.';
}

if ($city === '') {
	$errors[] = 'City is required.';
} elseif (safeLength($city) > 100) {
	$errors[] = 'City must be at most 100 characters.';
} elseif (!preg_match('/^[\p{L}\p{M} .\'-]+$/u', $city)) {
	$errors[] = 'City contains invalid characters.';
}

if (!empty($errors)) {
	redirectWithErrors($errors, array('name' => $name, 'email' => $email, 'city' => $city));
}

$stmt = $app->db->mysqli->prepare('INSERT INTO `users` (`name`, `email`, `city`, `created_at`) VALUES (?, ?, ?, NOW())');
if (!$stmt) {
	redirectWithErrors(array('Unable to save record right now. Please try again.'), array('name' => $name, 'email' => $email, 'city' => $city));
}

$stmt->bind_param('sss', $name, $email, $city);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
	redirectWithErrors(array('Unable to save record right now. Please try again.'), array('name' => $name, 'email' => $email, 'city' => $city));
}

header('Location: index.php', true, 303);
exit;
