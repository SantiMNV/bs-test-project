#!/bin/sh
set -e

# Ensure only one Apache MPM is enabled (required by mod_php)
a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

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
