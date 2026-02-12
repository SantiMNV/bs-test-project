<?php

/**
 * Loads .env values into runtime environment variables.
 * Existing environment values are preserved and never overwritten.
 */
function loadEnvFile($path) {
	if (!is_readable($path)) {
		return;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($lines === false) {
		return;
	}

	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || strpos($line, '#') === 0) {
			continue;
		}

		$separator = strpos($line, '=');
		if ($separator === false) {
			continue;
		}

		$key = trim(substr($line, 0, $separator));
		$value = trim(substr($line, $separator + 1));

		if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
			continue;
		}

		if (getenv($key) !== false) {
			continue;
		}

		$isDoubleQuoted = strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"';
		$isSingleQuoted = strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'";

		if ($isDoubleQuoted || $isSingleQuoted) {
			$value = substr($value, 1, -1);
		}

		if ($isDoubleQuoted) {
			$value = str_replace(
				array('\n', '\r', '\t', '\"', '\\\\'),
				array("\n", "\r", "\t", '"', '\\'),
				$value
			);
		}

		putenv($key . '=' . $value);
		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
	}
}
