<?php
// /config/database.php

define('MYSQL_HOST',    getenv('MYSQL_HOST')    ?: 'localhost');
define('MYSQL_DATABASE',    getenv('MYSQL_DATABASE')    ?: 'othTime_db');
define('MYSQL_USER',    getenv('MYSQL_USER')    ?: 'root');
define('MYSQL_PASSWORD',    getenv('MYSQL_PASSWORD')    ?: '');
define('MYSQL_CHARSET', 'utf8mb4');
