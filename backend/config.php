<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'anonymous_confessions';
$db_user = 'root';  // Replace with your MySQL username
$db_pass = '';  // Replace with your MySQL password

// Set error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('UTC');