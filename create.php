<?php

$app = require "./core/app.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: index.php', true, 303);
	exit;
}

function isAjaxRequest(): bool {
	$xRequestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
	$accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

	return $xRequestedWith === 'xmlhttprequest' || strpos($accept, 'application/json') !== false;
}

function respondJson(int $statusCode, array $payload): void {
	http_response_code($statusCode);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($payload);
	exit;
}

function redirectWithErrors(array $errors, array $oldInput): void {
	$_SESSION['form_errors'] = $errors;
	$_SESSION['old_input'] = $oldInput;
	header('Location: index.php', true, 303);
	exit;
}

function failRequest(array $errors, array $oldInput, bool $isAjax, int $statusCode = 422): void {
	if ($isAjax) {
		respondJson($statusCode, array(
			'ok' => false,
			'errors' => $errors
		));
	}

	redirectWithErrors($errors, $oldInput);
}

function safeLength(string $value): int {
	return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

$isAjax = isAjaxRequest();

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
	failRequest(array('The form has expired. Please refresh the page and try again.'), array(), $isAjax, 403);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$phoneInput = trim((string) ($_POST['phone_input'] ?? ''));

$oldInput = array(
	'name' => $name,
	'email' => $email,
	'city' => $city,
	'phone' => $phone !== '' ? $phone : $phoneInput
);
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

if ($phone === '') {
	$errors[] = 'Phone number is required.';
} elseif (!preg_match('/^\+[1-9]\d{7,14}$/', $phone)) {
	$errors[] = 'Phone number must be valid E.164 format (example: +14155552671).';
}

if (!empty($errors)) {
	failRequest($errors, $oldInput, $isAjax, 422);
}

$stmt = $app->db->mysqli->prepare('INSERT INTO `users` (`name`, `email`, `city`, `phone`, `created_at`) VALUES (?, ?, ?, ?, NOW())');
if (!$stmt) {
	failRequest(array('Unable to save record right now. Please try again.'), $oldInput, $isAjax, 500);
}

$stmt->bind_param('ssss', $name, $email, $city, $phone);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
	failRequest(array('Unable to save record right now. Please try again.'), $oldInput, $isAjax, 500);
}

if ($isAjax) {
	respondJson(201, array(
		'ok' => true,
		'user' => array(
			'name' => $name,
			'email' => $email,
			'city' => $city,
			'phone' => $phone
		)
	));
}

header('Location: index.php', true, 303);
exit;
