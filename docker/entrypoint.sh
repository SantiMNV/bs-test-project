#!/bin/sh
set -e

mkdir -p /var/www/html/config

cat > /var/www/html/config/database.php <<PHP
<?php

\$database = array(
    'address'  => '${MYSQL_HOST}',
    'username' => '${MYSQL_USER}',
    'password' => '${MYSQL_PASSWORD}',
    'database' => '${MYSQL_DATABASE}'
);
PHP

exec "$@"
