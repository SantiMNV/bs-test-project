#!/bin/sh
set -e

cat > /var/www/html/config/database.php <<PHP
<?php

$database = array(
    'address'  => '${DB_HOST:-db}',
    'username' => '${DB_USER:-app}',
    'password' => '${DB_PASSWORD:-app}',
    'database' => '${DB_NAME:-php_test}'
);
PHP

exec "$@"
