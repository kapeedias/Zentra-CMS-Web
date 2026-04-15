<?php
require_once __DIR__ . '/db.php';

// GoDaddy → manual credentials
$pdo = Database::getInstance(
    'localhost',
    'your_db_name',
    'your_db_user',
    'your_db_pass'
);

// Azure → environment variables
// $pdo = Database::getInstance();